<?php
/*************************************************************************************/
/*      Copyright (c) Franck Allimant, CQFDev                                        */
/*      email : thelia@cqfdev.fr                                                     */
/*      web : http://www.cqfdev.fr                                                   */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE      */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace MondialRelayPickupPoint\Form;

use MondialRelayPickupPoint\MondialRelayPickupPoint;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Form\BaseForm;

/**
 * @author Franck Allimant <franck@cqfdev.fr>
 */
class SettingsForm extends BaseForm
{
    protected function buildForm()
    {
        $this->formBuilder
            ->add(
                MondialRelayPickupPoint::CODE_ENSEIGNE,
                TextType::class,
                [
                    "constraints" => [new NotBlank()],
                    'label' => $this->translator->trans('Mondial Relay store code', [], MondialRelayPickupPoint::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => $this->translator->trans('This is the store code, as provided by Mondial Relay.', [], MondialRelayPickupPoint::DOMAIN_NAME)
                    ]

                ]
            )->add(
                MondialRelayPickupPoint::PRIVATE_KEY,
                TextType::class,
                [
                    "constraints" => [new NotBlank()],
                    'label' => $this->translator->trans('Private key', [], MondialRelayPickupPoint::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => $this->translator->trans('Your private key, as provided by Mondial Relay.', [], MondialRelayPickupPoint::DOMAIN_NAME)
                    ]

                ]
            )->add(
                MondialRelayPickupPoint::ALLOW_INSURANCE,
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => $this->translator->trans('Allow optional insurance', [], MondialRelayPickupPoint::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => $this->translator->trans('Check this box to allow an optionnal insurance selection depending on cart value.', [], MondialRelayPickupPoint::DOMAIN_NAME)
                    ]

                ]
            )->add(
                MondialRelayPickupPoint::WEBSERVICE_URL,
                TextType::class,
                [
                    'label' => $this->translator->trans('Mondial Relay Web service WSDL URL', [], MondialRelayPickupPoint::DOMAIN_NAME),
                    'label_attr' => [
                        'help' => $this->translator->trans('This is the URL of the Mondial Relay web service WSDL.', [], MondialRelayPickupPoint::DOMAIN_NAME)
                    ]
                ]
            );

    }
}
