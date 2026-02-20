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

namespace BaksDev\FourTochki\Repository\FourTochkiAuthorizationByProfile;

use BaksDev\FourTochki\Entity\Active\FourTochkiAuthActive;
use BaksDev\FourTochki\Entity\FourTochkiAuth;
use BaksDev\FourTochki\Entity\Login\FourTochkiAuthLogin;
use BaksDev\FourTochki\Entity\Password\FourTochkiAuthPassword;
use BaksDev\FourTochki\Entity\Profile\FourTochkiAuthProfile;
use BaksDev\FourTochki\Entity\Warehouse\FourTochkiAuthWarehouse;
use BaksDev\FourTochki\Type\Authorization\FourTochkiAuthorization;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;

final readonly class FourTochkiAuthorizationByProfileRepository implements FourTochkiAuthorizationByProfileInterface
{
    public function __construct(
        private DBALQueryBuilder $DBALQueryBuilder,
    ) {}

    public function getAuthorization(UserProfileUid $profile): FourTochkiAuthorization|false
    {
        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->from(FourTochkiAuthProfile::class, 'four_tochki_auth_profile')
            ->where('four_tochki_auth_profile.value = :profile')
            ->setParameter('profile', $profile, UserProfileUid::TYPE);

        $dbal
            ->join(
                'four_tochki_auth_profile',
                FourTochkiAuth::class,
                'four_tochki_auth',
                'four_tochki_auth_profile.event = four_tochki_auth.event'
            );

        $dbal->join(
            'four_tochki_auth',
            FourTochkiAuthActive::class,
            'four_tochki_auth_active',
            '
            four_tochki_auth_active.event = four_tochki_auth.event AND 
            four_tochki_auth_active.value IS TRUE',
        );

        $dbal
            ->join(
                'four_tochki_auth',
                FourTochkiAuthLogin::class,
                'four_tochki_auth_login',
                'four_tochki_auth_login.event = four_tochki_auth.event',
            );

        $dbal
            ->join(
                'four_tochki_auth',
                FourTochkiAuthPassword::class,
                'four_tochki_auth_password',
                'four_tochki_auth_password.event = four_tochki_auth.event',
            );

        $dbal
            ->join(
                'four_tochki_auth',
                FourTochkiAuthWarehouse::class,
                'four_tochki_auth_warehouse',
                'four_tochki_auth_warehouse.event = four_tochki_auth.event',
            );

        $dbal
            ->join(
                'four_tochki_auth',
                UserProfileInfo::class,
                'info',
                'info.profile = four_tochki_auth_profile.value AND info.status = :status',
            )
            ->setParameter(
                'status',
                UserProfileStatusActive::class,
                UserProfileStatus::TYPE,
            );

        $dbal
            ->select('four_tochki_auth.id AS profile')
            ->addSelect('four_tochki_auth_login.value AS login')
            ->addSelect('four_tochki_auth_password.value AS password')
            ->addSelect('four_tochki_auth_warehouse.value AS warehouse');

        /* Кешируем результат ORM */
        return $dbal
            ->enableCache('four_tochki')
            ->fetchHydrate(FourTochkiAuthorization::class);
    }
}
