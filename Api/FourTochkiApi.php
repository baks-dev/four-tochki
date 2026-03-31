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

use BaksDev\FourTochki\Repository\FourTochkiAuthorizationByProfile\FourTochkiAuthorizationByProfileInterface;
use BaksDev\FourTochki\Type\Authorization\FourTochkiAuthorization;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use SoapClient;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;

abstract class FourTochkiApi
{
    private const string PATH = 'http://api-b2b.4tochki.ru/WCF/ClientService.svc?wsdl';
    protected UserProfileUid|false $profile = false;
    private FourTochkiAuthorization|false $authorization = false;

    public function __construct(
        #[Autowire(env: 'APP_ENV')] private readonly string $environment,
        #[Target('fourTochkiLogger')] protected LoggerInterface $Logger,
        private readonly FourTochkiAuthorizationByProfileInterface $FourTochkiAuthorizationByProfileRepository,
    ) {}

    public function authorization(FourTochkiAuthorization|false $authorization): self
    {
        $this->authorization = $authorization;
        return $this;
    }

    public function tokenHttpClient(string $method, array $options = [])
    {
        /**
         * Если данные для авторизации не были прокинуты в виде объекта на прямую - должен быть прокинут профиль для их
         * поиска
         */
        if(false === $this->authorization)
        {
            if(false === $this->profile)
            {
                $this->Logger->critical(
                    'Не указан идентификатор профиля пользователя через вызов метода profile',
                    [self::class.':'.__LINE__],
                );

                throw new InvalidArgumentException(
                    'Не указан идентификатор профиля пользователя через вызов метода profile: ->profile($UserProfileUid)',
                );
            }


            /**
             * Если профиль указан, но по какой-то причине отсутствует значение свойства authorization - вызываем метод
             * profile для нахождения данных для авторизации
             */
            $this->profile($this->profile);
        }

        $client = new SoapClient(self::PATH);

        $options['login'] = $this->authorization->getLogin();
        $options['password'] = $this->authorization->getPassword();

        return $client->$method($options);
    }

    public function profile(UserProfile|UserProfileUid|string $profile): self
    {
        if($profile instanceof UserProfile)
        {
            $profile = $profile->getId();
        }

        if(is_string($profile))
        {
            $profile = new UserProfileUid($profile);
        }

        $this->profile = $profile;

        if(false === $this->authorization)
        {
            /** Находим данные для авторизации */
            $this->authorization = $this->FourTochkiAuthorizationByProfileRepository->getAuthorization($this->profile);
        }

        return $this;
    }

    public function getWarehouse(): int|false
    {
        return $this->authorization ? $this->authorization->getWarehouse() : false;
    }

    public function getProfile(): UserProfileUid|false
    {
        return $this->authorization ? $this->authorization->getProfile() : false;
    }

    public function getPercent(): string|false
    {
        return $this->authorization ? $this->authorization->getPercent() : false;
    }


    /**
     * Метод проверяет что окружение является PROD, тем самым позволяет выполнять операции запроса на сторонний сервис
     * ТОЛЬКО в PROD окружении
     */
    protected function isExecuteEnvironment(): bool
    {
        return $this->environment === 'prod';
    }
}