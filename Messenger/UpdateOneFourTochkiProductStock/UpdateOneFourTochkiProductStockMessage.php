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

use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\User\Type\Id\UserUid;

final readonly class UpdateOneFourTochkiProductStockMessage
{
    private string $product;

    private string $user;

    private string $profile;

    private string $invariable;

    public function __construct(
        ProductUid $product,
        private string $offer,
        private string $variation,
        private string $modification,
        UserUid $user,
        UserProfileUid $profile,
        ProductInvariableUid $invariable,
    )
    {
        $this->product = (string) $product;
        $this->user = (string) $user;
        $this->profile = (string) $profile;
        $this->invariable = (string) $invariable;
    }

    public function getProduct(): ProductUid
    {
        return new ProductUid($this->product);
    }

    public function getOfferValue(): string
    {
        return $this->offer;
    }

    public function getVariationValue(): string
    {
        return $this->variation;
    }

    public function getModificationValue(): string
    {
        return $this->modification;
    }

    public function getUser(): UserUid
    {
        return new UserUid($this->user);
    }

    public function getProfile(): UserProfileUid
    {
        return new UserProfileUid($this->profile);
    }

    public function getInvariable(): ProductInvariableUid
    {
        return new ProductInvariableUid($this->invariable);
    }
}