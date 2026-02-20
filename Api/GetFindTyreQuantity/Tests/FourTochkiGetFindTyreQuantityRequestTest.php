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

namespace BaksDev\FourTochki\Api\GetFindTyreQuantity\Tests;

use BaksDev\FourTochki\Api\GetFindTyreQuantity\FourTochkiGetFindTyreQuantityRequest;
use BaksDev\FourTochki\Repository\FourTochkiAuthorizationByProfile\FourTochkiAuthorizationByProfileInterface;
use BaksDev\FourTochki\UseCase\Admin\NewEdit\Tests\FourTochkiAuthNewTest;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Group('four-tochki')]
#[Group('four-tochki-api')]
final class FourTochkiGetFindTyreQuantityRequestTest extends KernelTestCase
{
    #[DependsOnClass(FourTochkiAuthNewTest::class)]
    public function testApi(): void
    {
        $FourTochkiAuthorizationByProfileRepository = self::getContainer()
            ->get(FourTochkiAuthorizationByProfileInterface::class);

        $profileUid = $_SERVER['TEST_PROFILE'] ?? UserProfileUid::TEST;


        /** Получаем данные для авторизации */
        $authorization = $FourTochkiAuthorizationByProfileRepository->getAuthorization(new UserProfileUid($profileUid));

        $FourTochkiGetFindTyreRequest = self::getContainer()->get(FourTochkiGetFindTyreQuantityRequest::class);

        /** @var FourTochkiGetFindTyreQuantityRequest $FourTochkiGetFindTyreRequest */
        $result = $FourTochkiGetFindTyreRequest
            ->authorization($authorization)
            ->setBrand('Triangle')
            ->setModel('EffeXSport TH202')
            ->setDiameter(19)
            ->setWidth(275)
            ->setHeight(50)
            ->setLoadIndex('112W')
            ->findTyre();

        self::assertTrue(is_int($result) || false === $result);
    }
}