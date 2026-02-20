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
use BaksDev\FourTochki\UseCase\Admin\Delete\FourTochkiAuthDeleteDTO;
use BaksDev\FourTochki\UseCase\Admin\Delete\FourTochkiAuthDeleteForm;
use BaksDev\FourTochki\UseCase\Admin\Delete\FourTochkiAuthDeleteHandler;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_FOUR_TOCHKI_AUTH_DELETE')]
final class DeleteController extends AbstractController
{
    #[Route('/admin/four-tochki/auth/delete/{id}', name: 'admin.delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        #[MapEntity] FourTochkiAuthEvent $event,
        FourTochkiAuthDeleteHandler $deleteHandler
    ): Response
    {
        $dto = new FourTochkiAuthDeleteDTO();

        $event->getDto($dto);

        $form = $this
            ->createForm(
                type: FourTochkiAuthDeleteForm::class,
                data: $dto,
                options: ['action' => $this->generateUrl('four-tochki:admin.delete', ['id' => $dto->getEvent()])]
            )
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('four_tochki_auth_delete'))
        {
            $this->refreshTokenForm($form);

            $fourTochkiAuth = $deleteHandler->handle($dto);

            if($fourTochkiAuth instanceof FourTochkiAuth)
            {
                $this->addFlash('breadcrumb.delete', 'success.delete', 'four-tochki.admin');

                return $this->redirectToRoute('four-tochki:admin.index');
            }

            $this->addFlash(
                'breadcrumb.delete',
                'danger.delete',
                'four-tochki.admin',
                $fourTochkiAuth,
            );

            return $this->redirectToRoute('four-tochki:admin.index', status: 400);
        }

        return $this->render([
            'form' => $form->createView(),
        ]);
    }
}
