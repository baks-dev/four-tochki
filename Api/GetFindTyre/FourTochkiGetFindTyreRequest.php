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

namespace BaksDev\FourTochki\Api\GetFindTyre;

use BaksDev\FourTochki\Api\FourTochkiApi;

final class FourTochkiGetFindTyreRequest extends FourTochkiApi
{
    const string METHOD = 'GetFindTyre';


    /** Метод получает по Api и возвращает остаток на складе продукта по его артикулу, а также цену на него
     *
     * @see https://b2b.4tochki.ru/Help/Page?url=GetFindTyre.html
     *
     * code_list (список объектов типа string) - Список САЕ (артикулов) для поиска.
     */
    public function findTyre(string $code): FourTochkiGetFindTyreResult|false
    {
        if(false === $this->getWarehouse())
        {
            return false;
        }

        $response = $this->tokenHttpClient(
            method: 'GetFindTyre',
            options: [
                'filter' => ['wrh_list' => [$this->getWarehouse()], 'code_list' => [$code]],
                'page' => 0,
            ],
        );

        if(
            true === empty($response) ||
            true === is_array($response) ||
            false === isset($response->GetFindTyreResult->price_rest_list->TyrePriceRest)
        )
        {
            return false;
        }

        $quantity = $response->GetFindTyreResult->price_rest_list->TyrePriceRest->whpr->wh_price_rest->rest;
        $price = $response->GetFindTyreResult->price_rest_list->TyrePriceRest->whpr->wh_price_rest->price;

        return new FourTochkiGetFindTyreResult($quantity, $price, $this->getPercent());
    }
}