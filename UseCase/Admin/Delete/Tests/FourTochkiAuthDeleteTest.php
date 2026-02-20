<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\FourTochki\UseCase\Admin\Delete\Tests;

use BaksDev\FourTochki\Entity\FourTochkiAuth;
use BaksDev\FourTochki\Entity\Event\FourTochkiAuthEvent;
use BaksDev\FourTochki\Type\Id\FourTochkiAuthUid;
use BaksDev\FourTochki\UseCase\Admin\Delete\FourTochkiAuthDeleteDTO;
use BaksDev\FourTochki\UseCase\Admin\Delete\FourTochkiAuthDeleteHandler;
use BaksDev\FourTochki\UseCase\Admin\NewEdit\Tests\FourTochkiAuthEditTest;
use BaksDev\FourTochki\UseCase\Admin\NewEdit\Tests\FourTochkiAuthNewTest;
use BaksDev\Products\Product\UseCase\Admin\Delete\Tests\ProductsProductDeleteAdminUseCaseTest;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Group('four-tochki')]
#[Group('four-tochki-usecase')]
final class FourTochkiAuthDeleteTest extends KernelTestCase
{
    #[DependsOnClass(FourTochkiAuthEditTest::class)]
    public function testDelete(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var EntityManagerInterface $EntityManager */
        $EntityManager = $container->get(EntityManagerInterface::class);

        /** Находим данные для авторизации по идентификатору профиля */
        $auth = $EntityManager
            ->getRepository(FourTochkiAuth::class)
            ->find(FourTochkiAuthUid::TEST);

        self::assertNotNull($auth);

        /** Находим активное событие */
        $activeEvent = $EntityManager
            ->getRepository(FourTochkiAuthEvent::class)
            ->find($auth->getEvent());

        self::assertNotNull($activeEvent);

        $deleteDTO = new FourTochkiAuthDeleteDTO();

        $activeEvent->getDto($deleteDTO);

        /** @var FourTochkiAuthDeleteHandler $FourTochkiAuthDeleteHandler */
        $FourTochkiAuthDeleteHandler = $container->get(FourTochkiAuthDeleteHandler::class);
        $deleteFourTochkiAuth = $FourTochkiAuthDeleteHandler->handle($deleteDTO);
        self::assertTrue($deleteFourTochkiAuth instanceof FourTochkiAuth);

        $fourTochkiAuth = $EntityManager
            ->getRepository(FourTochkiAuth::class)
            ->find($deleteFourTochkiAuth->getId());

        self::assertNull($fourTochkiAuth);
    }

    public static function tearDownAfterClass(): void
    {
        /** Удаляем тестовый продукт после завершения */
        ProductsProductDeleteAdminUseCaseTest::tearDownAfterClass();

        /** Удаляем тестовые данные для авторизации FourTochki */
        FourTochkiAuthNewTest::setUpBeforeClass();
    }
}
