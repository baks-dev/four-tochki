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

namespace BaksDev\FourTochki\UseCase\Admin\NewEdit;

use BaksDev\FourTochki\Entity\Event\FourTochkiAuthEventInterface;
use BaksDev\FourTochki\Type\Event\FourTochkiAuthEventUid;
use BaksDev\FourTochki\UseCase\Admin\NewEdit\Active\FourTochkiAuthActiveDTO;
use BaksDev\FourTochki\UseCase\Admin\NewEdit\Password\FourTochkiAuthPasswordDTO;
use BaksDev\FourTochki\UseCase\Admin\NewEdit\Login\FourTochkiAuthLoginDTO;
use BaksDev\FourTochki\UseCase\Admin\NewEdit\Profile\FourTochkiAuthProfileDTO;
use BaksDev\FourTochki\UseCase\Admin\NewEdit\Warehouse\FourTochkiAuthWarehouseDTO;
use Symfony\Component\Validator\Constraints as Assert;

/** @see FourTochkiAuthEvent */
final class FourTochkiAuthNewEditDTO implements FourTochkiAuthEventInterface
{
    /** Идентификатор события */
    #[Assert\Uuid]
    private ?FourTochkiAuthEventUid $id = null;

    /** ID настройки (профиль пользователя) */
    #[Assert\Valid]
    private FourTochkiAuthProfileDTO $profile;

    #[Assert\Valid]
    private FourTochkiAuthActiveDTO $active;

    #[Assert\Valid]
    private FourTochkiAuthLoginDTO $login;

    #[Assert\Valid]
    private FourTochkiAuthPasswordDTO $password;

    #[Assert\Valid]
    private FourTochkiAuthWarehouseDTO $warehouse;

    public function __construct()
    {
        $this->profile = new FourTochkiAuthProfileDTO();
        $this->active = new FourTochkiAuthActiveDTO;
        $this->login= new FourTochkiAuthLoginDTO;
        $this->password = new FourTochkiAuthPasswordDTO;
        $this->warehouse = new FourTochkiAuthWarehouseDTO();
    }

    public function setId(?FourTochkiAuthEventUid $id): void
    {
        $this->id = $id;
    }

    public function getEvent(): ?FourTochkiAuthEventUid
    {
        return $this->id;
    }

    public function getProfile(): FourTochkiAuthProfileDTO
    {
        return $this->profile;
    }

    public function setProfile(FourTochkiAuthProfileDTO $profile): self
    {
        $this->profile = $profile;
        return $this;
    }

    public function getActive(): FourTochkiAuthActiveDTO
    {
        return $this->active;
    }

    public function setActive(FourTochkiAuthActiveDTO $active): self
    {
        $this->active = $active;
        return $this;
    }

    public function getLogin(): FourTochkiAuthLoginDTO
    {
        return $this->login;
    }

    public function setLogin(FourTochkiAuthLoginDTO $login): self
    {
        $this->login = $login;
        return $this;
    }

    public function getPassword(): FourTochkiAuthPasswordDTO
    {
        return $this->password;
    }

    public function setPassword(FourTochkiAuthPasswordDTO $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getWarehouse(): FourTochkiAuthWarehouseDTO
    {
        return $this->warehouse;
    }

    public function setWarehouse(FourTochkiAuthWarehouseDTO $warehouse): self
    {
        $this->warehouse = $warehouse;
        return $this;
    }
}
