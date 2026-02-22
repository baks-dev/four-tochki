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

namespace BaksDev\FourTochki\Api\GetFindTyreQuantity;

use BaksDev\FourTochki\Api\FourTochkiApi;

final class FourTochkiGetFindTyreQuantityRequest extends FourTochkiApi
{
    const string METHOD = 'GetFindTyre';

    private ?string $brand = null;

    private ?string $model = null;

    private ?int $width = null;

    private ?int $height = null;

    private ?int $diameter = null;

    private ?string $loadIndex = null;

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;
        return $this;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;
        return $this;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;
        return $this;
    }

    public function setDiameter(int $diameter): self
    {
        $this->diameter = $diameter;
        return $this;
    }

    public function setLoadIndex(string $loadIndex): self
    {
        $this->loadIndex = $loadIndex;
        return $this;
    }

    /** Метод получает по Api и возвращает остаток на складе продукта по данным критериям
     *
     * @see https://b2b.4tochki.ru/Help/Page?url=GetFindTyre.html
     *
     * diameter_max, diameter_min - Диаметр. Интервал (включительно): [min , max].
     * height_max, height_min - Высота. Интервал (включительно): [min , max].
     * load_index (список объектов типа string) - Индекс нагрузки.
     * model_list (список объектов типа string) - Список моделей для поиска.
     * width_max, width_min - Ширина. Интервал (включительно): [min , max].
     */
    public function findTyre(): int|false
    {
        if(false === $this->getWarehouse())
        {
            return false;
        }

        $filter = ['wrh_list' => [$this->getWarehouse()]];

        if(false === empty($this->brand))
        {
            $filter['brand_list'] = [$this->brand];
        }

        if(false === empty($this->model))
        {
            $filter['model_list'] = [$this->model];
        }

        if(false === empty($this->width))
        {
            $filter['width_min'] = $this->width;
            $filter['width_max'] = $this->width;
        }

        if(false === empty($this->height))
        {
            $filter['height_min'] = $this->height;
            $filter['height_max'] = $this->height;
        }

        if(false === empty($this->diameter))
        {
            $filter['diameter_min'] = $this->diameter;
            $filter['diameter_max'] = $this->diameter;
        }

        if(false === empty($this->loadIndex))
        {
            $filter['load_index'] = $this->loadIndex;
        }

        $response = $this->tokenHttpClient(
            method: 'GetFindTyre',
            options: [
                'filter' => $filter,
                'page' => 0,
            ],
        );

        if(true === empty($response) || false === isset($response->GetFindTyreResult->price_rest_list->TyrePriceRest))
        {
            return false;
        }

        $response = $response->GetFindTyreResult->price_rest_list->TyrePriceRest;

        /**
         * Нам нужно проверить, что $response является именно объектом - если это массив, значит, метод нашел несколько
         * моделей по данным критериям, и во избежание путаницы такая модель пропускается
         */
        return (true === is_object($response)) ? $response->whpr->wh_price_rest->rest : false;
    }
}