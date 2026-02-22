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

namespace BaksDev\FourTochki\Api;

use BaksDev\FourTochki\Type\Authorization\FourTochkiAuthorization;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use InvalidArgumentException;
use SoapClient;

abstract class FourTochkiApi
{
    private FourTochkiAuthorization|false $authorization = false;

    private const string PATH = 'http://api-b2b.4tochki.ru/WCF/ClientService.svc?wsdl';

    public function authorization(FourTochkiAuthorization|false $authorization): self
    {
        $this->authorization = $authorization;
        return $this;
    }

    public function tokenHttpClient(string $method, array $options = [])
    {
        if(false === $this->authorization)
        {
            throw new InvalidArgumentException('Не указан обязательный параметр authorization');
        }

        $client = new SoapClient(self::PATH);

        $options['login'] = $this->authorization->getLogin();
        $options['password'] = $this->authorization->getPassword();

        return $client->$method($options);
    }

    public function getWarehouse(): int|false
    {
        return $this->authorization ? $this->authorization->getWarehouse() : false;
    }

    public function getProfile(): UserProfileUid|false
    {
        return $this->authorization ? $this->authorization->getProfile() : false;
    }


}