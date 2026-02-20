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

namespace BaksDev\FourTochki\Repository\AllFourTochkiAuth;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Entity\Status\AccountStatus;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\FourTochki\Entity\FourTochkiAuth;
use BaksDev\FourTochki\Entity\Active\FourTochkiAuthActive;
use BaksDev\FourTochki\Entity\Profile\FourTochkiAuthProfile;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Avatar\UserProfileAvatar;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

final class AllFourTochkiAuthRepository implements AllFourTochkiAuthInterface
{
    private ?SearchDTO $search = null;

    private ?UserProfileUid $profile = null;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly PaginatorInterface $paginator,
    ) {}

    public function profile(UserProfileUid|string $profile): self
    {
        if(is_string($profile))
        {
            $profile = new UserProfileUid($profile);
        }

        $this->profile = $profile;

        return $this;
    }

    public function search(SearchDTO $search): self
    {
        $this->search = $search;

        return $this;
    }

    public function findPaginator(): PaginatorInterface
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->select('four_tochki_auth.id')
            ->addSelect('four_tochki_auth.event')
            ->from(FourTochkiAuth::class, 'four_tochki_auth');


        $dbal
            ->addSelect('four_tochki_auth_profile.value as users_profile_uid')
            ->join(
                'four_tochki_auth',
                FourTochkiAuthProfile::class,
                'four_tochki_auth_profile',
                'four_tochki_auth_profile.event = four_tochki_auth.event'
                .($this->profile instanceof UserProfileUid ? ' AND four_tochki_auth_profile.value = :profile' : ''),
            );

        /** Если не админ - только данные профиля */
        if($this->profile instanceof UserProfileUid)
        {
            $dbal
                ->setParameter(
                    key: 'profile',
                    value: $this->profile,
                    type: UserProfileUid::TYPE,
                );
        }

        $dbal
            ->addSelect('four_tochki_auth_active.value AS active')
            ->leftJoin(
                'four_tochki_auth',
                FourTochkiAuthActive::class,
                'four_tochki_auth_active',
                "four_tochki_auth_active.event = four_tochki_auth.event",
            );


        // ОТВЕТСТВЕННЫЙ

        // UserProfile
        $dbal
            ->addSelect('users_profile.event as users_profile_event')
            ->leftJoin(
                'four_tochki_auth',
                UserProfile::class,
                'users_profile',
                'users_profile.id = four_tochki_auth_profile.value',
            );


        // Info
        $dbal
            ->addSelect('users_profile_info.status as users_profile_status')
            ->leftJoin(
                'four_tochki_auth',
                UserProfileInfo::class,
                'users_profile_info',
                'users_profile_info.profile = four_tochki_auth_profile.value',
            );

        // Personal
        $dbal
            ->addSelect('users_profile_personal.username AS users_profile_username')
            ->leftJoin(
                'users_profile',
                UserProfilePersonal::class,
                'users_profile_personal',
                'users_profile_personal.event = users_profile.event',
            );

        // Avatar
        $dbal
            ->addSelect("CASE
                WHEN users_profile_avatar.name IS NOT NULL
                THEN CONCAT ( '/upload/".$dbal->table(UserProfileAvatar::class)."' , '/', users_profile_avatar.name)
                ELSE NULL
            END AS users_profile_avatar")
            ->addSelect("users_profile_avatar.ext AS users_profile_avatar_ext")
            ->addSelect('users_profile_avatar.cdn AS users_profile_avatar_cdn')
            ->leftJoin(
                'users_profile',
                UserProfileAvatar::class,
                'users_profile_avatar',
                'users_profile_avatar.event = users_profile.event',
            );

        /** ACCOUNT */
        $dbal->leftJoin(
            'users_profile_info',
            Account::class,
            'account',
            'account.id = users_profile_info.usr',
        );

        $dbal
            ->addSelect('account_event.email AS account_email')
            ->leftJoin(
                'account',
                AccountEvent::class,
                'account_event',
                'account_event.id = account.event AND account_event.account = account.id',
            );

        $dbal
            ->addSelect('account_status.status as account_status')
            ->leftJoin(
                'account_event',
                AccountStatus::class,
                'account_status',
                'account_status.event = account_event.id',
            );

        /* Поиск */
        if($this->search?->getQuery())
        {
            $dbal
                ->createSearchQueryBuilder($this->search)
                ->addSearchEqualUid('four_tochki_auth.id')
                ->addSearchEqualUid('four_tochki_auth.event')
                ->addSearchLike('account_event.email')
                ->addSearchLike('users_profile_personal.username');
        }

        return $this->paginator->fetchAllHydrate($dbal, AllFourTochkiAuthResult::class);
    }
}
