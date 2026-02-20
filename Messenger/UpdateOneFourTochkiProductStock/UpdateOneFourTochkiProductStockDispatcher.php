<?php
/*
 * Copyright 2026.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\FourTochki\Messenger\UpdateOneFourTochkiProductStock;

use BaksDev\Field\Pack\Brand\Type\BrandField;
use BaksDev\Field\Pack\Model\Type\ModelField;
use BaksDev\FourTochki\Api\GetFindTyreQuantity\FourTochkiGetFindTyreQuantityRequest;
use BaksDev\FourTochki\Repository\FourTochkiAuthorizationByProfile\FourTochkiAuthorizationByProfileInterface;
use BaksDev\Products\Product\Repository\ProductDetailByValue\ProductDetailByValueInterface;
use BaksDev\Products\Stocks\Entity\Total\ProductStockTotal;
use BaksDev\Products\Stocks\Repository\ProductStocksTotalStorage\ProductStocksTotalStorageInterface;
use BaksDev\Products\Stocks\UseCase\Admin\EditTotal\ProductStockTotalEditDTO;
use BaksDev\Products\Stocks\UseCase\Admin\EditTotal\ProductStockTotalEditHandler;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateOneFourTochkiProductStockDispatcher
{
    public function __construct(
        #[Target('fourTochkiLogger')] private LoggerInterface $Logger,
        private FourTochkiGetFindTyreQuantityRequest $FourTochkiGetFindTyreRequest,
        private FourTochkiAuthorizationByProfileInterface $FourTochkiAuthorizationByProfileRepository,
        private ProductDetailByValueInterface $ProductDetailByValueRepository,
        private ProductStockTotalEditHandler $ProductStockTotalEditHandler,
        private ProductStocksTotalStorageInterface $ProductStocksTotalStorageRepository,
        private EntityManagerInterface $EntityManager,
    ) {}

    public function __invoke(UpdateOneFourTochkiProductStockMessage $message): void
    {
        /** Находим всю необходимую информацию о продукте по его invariable */
        $productDetailByInvariableResult = $this->ProductDetailByValueRepository
            ->byProduct($message->getProduct())
            ->byOfferValue($message->getOfferValue())
            ->byVariationValue($message->getVariationValue())
            ->byModificationValue($message->getModificationValue())
            ->find();

        if(false === $productDetailByInvariableResult)
        {
            $this->Logger->critical(
                'Информация о продукте не была найдена',
                [var_export($message, true), self::class.':'.__LINE__]
            );
            return;
        }


        /** Находим все поля продукта */
        $categorySectionFields = $productDetailByInvariableResult->getCategorySectionField();

        if(true === empty($categorySectionFields))
        {
            $this->Logger->warning(
                'Поля категории для продукта не были найдены',
                [var_export($message, true), self::class.':'.__LINE__]
            );
            return;
        }


        /** Ищем поле типа "Модель"  */
        $model = array_find($categorySectionFields, function (Object $categorySectionField): bool
        {
            return $categorySectionField->field_type === ModelField::TYPE;
        });

        if(false === isset($model->field_value))
        {
            /** Если у продукта нет поля типа "Модель" - пропускаем его */
            $this->Logger->warning(
                sprintf(
                    'Для продукта %s не заполнено поле Модель',
                    $productDetailByInvariableResult->getProductName()
                ),
                [var_export($message, true), self::class.':'.__LINE__]);
            return;
        }


        /** Ищем поля типа "Бренд" */
        $brand = array_find($categorySectionFields, function (Object $categorySectionField): bool
        {
            return $categorySectionField->field_type === BrandField::TYPE;
        });

        if(false === isset($brand->field_value))
        {
            /** Если у продукта нет поля типа "Бренд" - пропускаем его */
            $this->Logger->warning(
                sprintf(
                    'Для продукта %s не заполнено поле Бренд',
                    $productDetailByInvariableResult->getProductName()
                ),
                [var_export($message, true), self::class.':'.__LINE__]
            );
            return;
        }

        $brand = $brand->field_value;


        /** Получаем данные для авторизации */
        $authorization = $this->FourTochkiAuthorizationByProfileRepository->getAuthorization($message->getProfile());

        if(false === $authorization)
        {
            $this->Logger->warning(
                'Данные для авторизации данного профиля не были найдены',
                [var_export($message, true), self::class.':'.__LINE__]
            );
            return;
        }

        $model = $model->field_value;

        $quantity = $this->FourTochkiGetFindTyreRequest
            ->authorization($authorization)
            ->setDiameter((int) $productDetailByInvariableResult->getProductOfferValue())
            ->setWidth((int) $productDetailByInvariableResult->getProductVariationValue())
            ->setHeight((int) $productDetailByInvariableResult->getProductModificationValue())
            ->setLoadIndex($productDetailByInvariableResult->getProductModificationPostfix())
            ->setBrand($brand)
            ->setModel($model)
            ->findTyre();


        if(false === $quantity)
        {
            $this->Logger->warning(
                sprintf('Данная модель %s %s не была найдена на складах 4tochki', $brand, $model),
                [var_export($message, true), self::class.':'.__LINE__]
            );
            return;
        }

        $productStockTotalEditDTO = new ProductStockTotalEditDTO();


        /** Получаем текущий складской остаток для данных продукта и профиля, если таковой имеется */
        $productStocksTotal = $this->ProductStocksTotalStorageRepository
            ->product($productDetailByInvariableResult->getProductId())
            ->offer($productDetailByInvariableResult->getProductOfferConst())
            ->variation($productDetailByInvariableResult->getProductVariationConst())
            ->modification($productDetailByInvariableResult->getProductModificationConst())
            ->profile($message->getProfile())
            ->find();


        /** Если отсутствует место складирования - создаем на указанный профиль пользователя */
        if(false === ($productStocksTotal instanceof ProductStockTotal))
        {

            $productStocksTotal = new ProductStockTotal(
                $message->getUser(),
                $message->getProfile(),
                $productDetailByInvariableResult->getProductId(),
                $productDetailByInvariableResult->getProductOfferConst(),
                $productDetailByInvariableResult->getProductVariationConst(),
                $productDetailByInvariableResult->getProductModificationConst(),
                '4tochki',
            );

            $this->EntityManager->persist($productStocksTotal);
            $this->EntityManager->flush();

            $this->Logger->info(
                'Место складирования профиля не найдено! Создали новое место для указанной продукции',
                [
                    self::class.':'.__LINE__,
                    'profile' => (string) $message->getProfile(),
                ],
            );
        }


        $productStocksTotal->getDto($productStockTotalEditDTO);

        $productStockTotalEditDTO
            ->setTotal($quantity)
            ->setStorage('4tochki');


        /** Обновляем остаток на складе */
        $handle = $this->ProductStockTotalEditHandler->handle($productStockTotalEditDTO);

        if(false === ($handle instanceof ProductStockTotal))
        {
            $this->Logger->critical(
                'Не удалось обновить остаток продукта на складе',
                [var_export($message, true), self::class.':'.__LINE__]
            );

            return;
        }

        $this->Logger->info(
            'Остаток продукции на складе успешно обновлен',
            [var_export($message, true), self::class.':'.__LINE__]
        );
    }
}