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

namespace BaksDev\FourTochki\Controller\Admin;

use BaksDev\FourTochki\Entity\FourTochkiAuth;
use BaksDev\FourTochki\Entity\Event\FourTochkiAuthEvent;
use BaksDev\FourTochki\Type\Event\FourTochkiAuthEventUid;
use BaksDev\FourTochki\UseCase\Admin\NewEdit\FourTochkiAuthNewEditDTO;
use BaksDev\FourTochki\UseCase\Admin\NewEdit\FourTochkiAuthNewEditForm;
use BaksDev\FourTochki\UseCase\Admin\NewEdit\FourTochkiAuthNewEditHandler;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_FOUR_TOCHKI_AUTH_EDIT')]
final class EditController extends AbstractController
{
    #[Route('/admin/four-tochki/auth/edit/{id}', name: 'admin.newedit.edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity] FourTochkiAuthEvent $fourTochkiAuthEvent,
        FourTochkiAuthNewEditHandler $FourTochkiAuthNewEditHandler
    ): Response
    {
        $fourTochkiAuthNewEditDTO = new FourTochkiAuthNewEditDTO();

        /** Запрещаем редактировать чужые данные для авторизации */
        if(
            $this->isAdmin() === true || $fourTochkiAuthEvent->getProfile()->equals($this->getProfileUid()) === true)
        {
            $fourTochkiAuthEvent->getDto($fourTochkiAuthNewEditDTO);
        }

        if($request->getMethod() === 'GET')
        {
            $fourTochkiAuthNewEditDTO
                ->getPassword()
                ->hiddenSecret();
        }

        $form = $this
            ->createForm(
                type: FourTochkiAuthNewEditForm::class,
                data: $fourTochkiAuthNewEditDTO,
                options: ['action' => $this->generateUrl(
                    'four-tochki:admin.newedit.edit',
                    ['id' => $fourTochkiAuthNewEditDTO->getEvent() ?: new FourTochkiAuthEventUid()]
                )],
            )
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('four_tochki_auth_newedit'))
        {
            $this->refreshTokenForm($form);

            /** Запрещаем редактировать чужые данные для авторизации */
            if(
                $this->isAdmin() === false &&
                $this->getProfileUid()?->equals($fourTochkiAuthNewEditDTO->getProfile()->getValue()) !== true
            )
            {
                $this->addFlash(
                    'breadcrumb.edit',
                    'danger.edit',
                    'four-tochki.admin',
                    '404'
                );
                return $this->redirectToReferer();
            }

            $fourTochkiAuth = $FourTochkiAuthNewEditHandler->handle($fourTochkiAuthNewEditDTO);

            if($fourTochkiAuth instanceof FourTochkiAuth)
            {
                $this->addFlash('breadcrumb.edit', 'success.edit', 'four-tochki.admin');

                return $this->redirectToRoute('four-tochki:admin.index');
            }

            $this->addFlash(
                'breadcrumb.edit',
                'danger.edit',
                'four-tochki.admin',
                $fourTochkiAuth
            );

            return $this->redirectToReferer();
        }

        return $this->render(['form' => $form->createView()]);
    }
}
