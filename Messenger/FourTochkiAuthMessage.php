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

namespace BaksDev\FourTochki\Messenger;

use BaksDev\FourTochki\Type\Event\FourTochkiAuthEventUid;
use BaksDev\FourTochki\Type\Id\FourTochkiAuthUid;

final readonly class FourTochkiAuthMessage
{
    public function __construct(
        private FourTochkiAuthUid $id,
        private FourTochkiAuthEventUid $currentEvent,
        private ?FourTochkiAuthEventUid $lastEvent = null,
    ) {}

    /**
     * Идентификатор
     */
    public function getId(): FourTochkiAuthUid
    {
        return $this->id;
    }

    /**
     * Идентификатор события
     */
    public function getCurrentEvent(): FourTochkiAuthEventUid
    {
        return $this->currentEvent;
    }

    /**
     * Идентификатор предыдущего события
     */
    public function getLastEvent(): ?FourTochkiAuthEventUid
    {
        return $this->lastEvent;
    }
}
