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

use MondialRelayPickupPoint\MondialRelayPickupPoint;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Template\ParserContext;
use Thelia\Log\Tlog;
use Thelia\Tools\URL;

/**
 * @author Franck Allimant <franck@cqfdev.fr>
 */
class ConfigurationController extends BaseAdminController
{
    public function saveAction(ParserContext $parserContext)
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, 'MondialRelayPickupPoint', AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm('mondialrelaypickuppoint.settings_form');

        $errorMessage = false;

        try {
            $viewForm = $this->validateForm($form);

            $data = $viewForm->getData();

            foreach ($data as $name => $value) {
                MondialRelayPickupPoint::setConfigValue($name, $value);
            }
        } catch (\Exception $ex) {
            $errorMessage = $ex->getMessage();

            Tlog::getInstance()->error("Failed to validate configuration form: $errorMessage");

            $form->setErrorMessage($errorMessage);
            $parserContext->addForm($form);
            $parserContext->setGeneralError($errorMessage);

            return $this->render(
                "module-configure",
                ["module_code" => MondialRelayPickupPoint::getModuleCode()]
            );
        }

        return $this->generateRedirect(URL::getInstance()->absoluteUrl('/admin/module/MondialRelayPickupPoint'));
    }
}
