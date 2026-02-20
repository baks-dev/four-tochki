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

namespace BaksDev\FourTochki\Entity\Event;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\FourTochki\Entity\FourTochkiAuth;
use BaksDev\FourTochki\Entity\Active\FourTochkiAuthActive;
use BaksDev\FourTochki\Entity\Login\FourTochkiAuthLogin;
use BaksDev\FourTochki\Entity\Modify\FourTochkiAuthModify;
use BaksDev\FourTochki\Entity\Password\FourTochkiAuthPassword;
use BaksDev\FourTochki\Entity\Profile\FourTochkiAuthProfile;
use BaksDev\FourTochki\Entity\Warehouse\FourTochkiAuthWarehouse;
use BaksDev\FourTochki\Type\Event\FourTochkiAuthEventUid;
use BaksDev\FourTochki\Type\Id\FourTochkiAuthUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'four_tochki_auth_event')]
class FourTochkiAuthEvent extends EntityEvent
{
    /** Идентификатор События */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: FourTochkiAuthEventUid::TYPE)]
    private FourTochkiAuthEventUid $id;

    /** ID профиля пользователя */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: FourTochkiAuthUid::TYPE)]
    private FourTochkiAuthUid $main;

    /** Идентификатор профиля владельца */
    #[ORM\OneToOne(targetEntity: FourTochkiAuthProfile::class, mappedBy: 'event', cascade: ['all'])]
    private ?FourTochkiAuthProfile $profile = null;

    /** Логин для авторизации */
    #[ORM\OneToOne(targetEntity: FourTochkiAuthLogin::class, mappedBy: 'event', cascade: ['all'])]
    private ?FourTochkiAuthLogin $login = null;

    /** Пароль для авторизации */
    #[ORM\OneToOne(targetEntity: FourTochkiAuthPassword::class, mappedBy: 'event', cascade: ['all'])]
    private ?FourTochkiAuthPassword $password = null;

    /** Настройка для администратора - вкл/выкл профиль 4tochki */
    #[ORM\OneToOne(targetEntity: FourTochkiAuthActive::class, mappedBy: 'event', cascade: ['all'])]
    private ?FourTochkiAuthActive $active = null;

    /** Идентификатор склада  */
    #[ORM\OneToOne(targetEntity: FourTochkiAuthWarehouse::class, mappedBy: 'event', cascade: ['all'])]
    private ?FourTochkiAuthWarehouse $warehouse = null;

    #[ORM\OneToOne(targetEntity: FourTochkiAuthModify::class, mappedBy: 'event', cascade: ['all'])]
    private FourTochkiAuthModify $modify;

    public function __construct()
    {
        $this->id = new FourTochkiAuthEventUid();
        $this->modify = new FourTochkiAuthModify($this);
    }

    public function __clone()
    {
        $this->id = clone new FourTochkiAuthEventUid();
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): FourTochkiAuthEventUid
    {
        return $this->id;
    }

    public function getProfile(): UserProfileUid
    {
        return $this->profile?->getValue();
    }

    public function setMain(FourTochkiAuth|FourTochkiAuthUid $main): self
    {
        $this->main = $main instanceof FourTochkiAuth ? $main->getId() : $main;

        return $this;
    }

    public function getDto($dto): mixed
    {
        if($dto instanceof FourTochkiAuthEventInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if($dto instanceof FourTochkiAuthEventInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
}
