<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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
use BaksDev\FourTochki\Type\Event\FourTochkiAuthEventUid;
use BaksDev\FourTochki\UseCase\Admin\NewEdit\FourTochkiAuthNewEditDTO;
use BaksDev\FourTochki\UseCase\Admin\NewEdit\FourTochkiAuthNewEditHandler;
use BaksDev\Core\Type\Modify\Modify\ModifyActionUpdate;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Group('four-tochki')]
#[Group('four-tochki-usecase')]
final class FourTochkiAuthEditTest extends KernelTestCase
{
    #[DependsOnClass(FourTochkiAuthNewTest::class)]
    public function testEdit(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var EntityManagerInterface $EntityManager */
        $EntityManager = $container->get(EntityManagerInterface::class);

        /** Находим активное событие */
        $activeEvent = $EntityManager
            ->getRepository(FourTochkiAuthEvent::class)
            ->find(FourTochkiAuthEventUid::TEST);

        self::assertNotNull($activeEvent);

        $fourTochkiAuthNewEditDTO = new FourTochkiAuthNewEditDTO();

        $activeEvent->getDto($fourTochkiAuthNewEditDTO);

        /** @var FourTochkiAuthNewEditHandler $FourTochkiAuthNewEditHandler */
        $FourTochkiAuthNewEditHandler = $container->get(FourTochkiAuthNewEditHandler::class);
        $editFourTochkiAuth = $FourTochkiAuthNewEditHandler->handle($fourTochkiAuthNewEditDTO);
        self::assertTrue($editFourTochkiAuth instanceof FourTochkiAuth);

        $modifier = $EntityManager
            ->getRepository(FourTochkiAuthModify::class)
            ->find($editFourTochkiAuth->getEvent());

        self::assertTrue($modifier->equals(ModifyActionUpdate::ACTION));
    }
}
