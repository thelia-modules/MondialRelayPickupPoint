<?php
/*************************************************************************************/
/*      Copyright (c) Franck Allimant, CQFDev                                        */
/*      email : thelia@cqfdev.fr                                                     */
/*      web : http://www.cqfdev.fr                                                   */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE      */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace MondialRelayPickupPoint\Controller\BackOffice;


use MondialRelayPickupPoint\Model\MondialRelayPickupPointZoneConfiguration;
use MondialRelayPickupPoint\Model\MondialRelayPickupPointZoneConfigurationQuery;
use MondialRelayPickupPoint\MondialRelayPickupPoint;
use MondialRelayPickupPoint\Form\PriceAttributesUpdateForm;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Template\ParserContext;
use Thelia\Log\Tlog;
use Thelia\Tools\URL;

/**
 * @author Franck Allimant <franck@cqfdev.fr>
 */
class AreaAttributesController extends BaseAdminController
{
    public function saveAction($areaId, $moduleId, ParserContext $parserContext)
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'MondialRelayPickupPoint', AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm(PriceAttributesUpdateForm::getName());

        $errorMessage = false;

        try {
            $viewForm = $this->validateForm($form);

            $data = $viewForm->getData();

            if (null === $zoneConfig = MondialRelayPickupPointZoneConfigurationQuery::create()->findOneByAreaId($areaId)) {
                $zoneConfig = new MondialRelayPickupPointZoneConfiguration();
            }

            $zoneConfig
                ->setAreaId($areaId)
                ->setDeliveryTime($data['delivery_time'])
                ->save();

        } catch (\Exception $ex) {
            $errorMessage = $ex->getMessage();

            Tlog::getInstance()->error("Failed to validate area attributes form: $errorMessage");
            $form->setErrorMessage($errorMessage);
            $parserContext->addForm($form);
            $parserContext->setGeneralError($errorMessage);

            return $this->generateRedirect(URL::getInstance()->absoluteUrl('/admin/module/MondialRelayPickupPoint'));
        }

        return $this->generateRedirect(URL::getInstance()->absoluteUrl('/admin/module/MondialRelayPickupPoint'));
    }
}
