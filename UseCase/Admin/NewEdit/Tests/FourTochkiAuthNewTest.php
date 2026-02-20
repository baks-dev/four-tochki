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

namespace BaksDev\FourTochki\UseCase\Admin\NewEdit\Tests;

use BaksDev\FourTochki\Entity\FourTochkiAuth;
use BaksDev\FourTochki\Entity\Event\FourTochkiAuthEvent;
use BaksDev\FourTochki\Entity\Modify\FourTochkiAuthModify;
use BaksDev\FourTochki\Type\Id\FourTochkiAuthUid;
use BaksDev\FourTochki\UseCase\Admin\NewEdit\Active\FourTochkiAuthActiveDTO;
use BaksDev\FourTochki\UseCase\Admin\NewEdit\FourTochkiAuthNewEditDTO;
use BaksDev\FourTochki\UseCase\Admin\NewEdit\FourTochkiAuthNewEditHandler;
use BaksDev\FourTochki\UseCase\Admin\NewEdit\Password\FourTochkiAuthPasswordDTO;
use BaksDev\FourTochki\UseCase\Admin\NewEdit\Login\FourTochkiAuthLoginDTO;
use BaksDev\Core\Type\Modify\Modify\ModifyActionNew;
use BaksDev\FourTochki\UseCase\Admin\NewEdit\Profile\FourTochkiAuthProfileDTO;
use BaksDev\FourTochki\UseCase\Admin\NewEdit\Warehouse\FourTochkiAuthWarehouseDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Tests\ProductsProductNewAdminUseCaseTest;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[When(env: 'test')]
#[Group('four-tochki')]
#[Group('four-tochki-api')]
#[Group('four-tochki-controller')]
#[Group('four-tochki-dispatcher')]
#[Group('four-tochki-repository')]
#[Group('four-tochki-usecase')]
final class FourTochkiAuthNewTest extends KernelTestCase
{
    public static function setUpBeforeClass(): void
    {
        // Бросаем событие консольной комманды
        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        $event = new ConsoleCommandEvent(new Command(), new StringInput(''), new NullOutput());
        $dispatcher->dispatch($event, 'console.command');

        /** @var EntityManagerInterface $EntityManager */
        $EntityManager = self::getContainer()->get(EntityManagerInterface::class);

        $dromAuth = $EntityManager
            ->getRepository(FourTochkiAuth::class)
            ->find(FourTochkiAuthUid::TEST);

        if($dromAuth)
        {
            $EntityManager->remove($dromAuth);
        }

        $dromAuthEvent = $EntityManager
            ->getRepository(FourTochkiAuthEvent::class)
            ->findBy(['main' => FourTochkiAuthUid::TEST]);

        foreach($dromAuthEvent as $event)
        {
            $EntityManager->remove($event);
        }

        $EntityManager->flush();
        $EntityManager->clear();
    }

    public function testNew(): void
    {
        $dromAuthNewEditDTO = new FourTochkiAuthNewEditDTO();

        // FourTochkiAuthProfileDTO
        $dromAuthProfileDTO = new FourTochkiAuthProfileDTO();
        $dromAuthProfileDTO->setValue(new UserProfileUid(UserProfileUid::TEST));
        self::assertTrue($dromAuthProfileDTO->getValue()->equals(UserProfileUid::TEST));

        $dromAuthNewEditDTO->setProfile($dromAuthProfileDTO);


        // FourTochkiAuthActiveDTO
        $dromAuthActiveDTO = new FourTochkiAuthActiveDTO();
        $dromAuthActiveDTO->setValue(true);
        self::assertTrue($dromAuthActiveDTO->getValue());

        $dromAuthNewEditDTO->setActive($dromAuthActiveDTO);


        // FourTochkiAuthLoginDTO
        $dromAuthLoginDTO = new FourTochkiAuthLoginDTO();
        $dromAuthLoginDTO->setValue('FourTochkiAuthLoginDTO');
        self::assertSame('FourTochkiAuthLoginDTO', $dromAuthLoginDTO->getValue());

        $dromAuthNewEditDTO->setLogin($dromAuthLoginDTO);


        // FourTochkiAuthPasswordDTO
        $dromAuthPasswordDTO = new FourTochkiAuthPasswordDTO();
        $dromAuthPasswordDTO->setValue('FourTochkiAuthPasswordDTO');
        self::assertSame('FourTochkiAuthPasswordDTO', $dromAuthPasswordDTO->getValue());

        $dromAuthNewEditDTO->setPassword($dromAuthPasswordDTO);


        // FourTochkiAuthWarehouseDTO
        $dromAuthWarehouseDTO = new FourTochkiAuthWarehouseDTO();
        $dromAuthWarehouseDTO->setValue(1);
        self::assertSame(1, $dromAuthWarehouseDTO->getValue());

        $dromAuthNewEditDTO->setWarehouse($dromAuthWarehouseDTO);


        /** @var FourTochkiAuthNewEditHandler $FourTochkiAuthNewEditHandler */
        $FourTochkiAuthNewEditHandler = self::getContainer()->get(FourTochkiAuthNewEditHandler::class);
        $newFourTochkiAuth = $FourTochkiAuthNewEditHandler->handle($dromAuthNewEditDTO);
        self::assertInstanceOf(FourTochkiAuth::class, $newFourTochkiAuth);


        /** @var EntityManagerInterface $EntityManager */
        $EntityManager = self::getContainer()->get(EntityManagerInterface::class);

        /** Проверка соответствия модификатора */
        $modifier = $EntityManager
            ->getRepository(FourTochkiAuthModify::class)
            ->find($newFourTochkiAuth->getEvent());

        self::assertTrue($modifier->equals(ModifyActionNew::ACTION));
    }
}
