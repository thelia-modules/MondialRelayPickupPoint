<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia	                                                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : info@thelia.net                                                      */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 3 of the License                */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*	    along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace MondialRelayPickupPoint\Form;

use MondialRelayPickupPoint\Model\Base\MondialRelayPickupPointFreeshippingQuery;
use MondialRelayPickupPoint\MondialRelayPickupPoint;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;

class FreeShippingForm extends BaseForm
{
    /**
     *
     * in this function you add all the fields you need for your Form.
     * Form this you have to call add method on $this->formBuilder attribute :
     *
     * $this->formBuilder->add("name", "text")
     *   ->add("email", "email", array(
     *           "attr" => array(
     *               "class" => "field"
     *           ),
     *           "label" => "email",
     *           "constraints" => array(
     *               new \Symfony\Component\Validator\Constraints\NotBlank()
     *           )
     *       )
     *   )
     *   ->add('age', 'integer');
     *
     * @return null
     */
    protected function buildForm()
    {
        $this->formBuilder
            ->add(
                'freeshipping',
                CheckboxType::class,
                [
                    'label'     => Translator::getInstance()->trans("Activate free shipping: ", [], MondialRelayPickupPoint::DOMAIN_NAME)
                ]
            )
            ->add(
                'freeshipping_from',
                NumberType::class,
                [
                    'required'  => false,
                    'label'     => Translator::getInstance()->trans("Free shipping from: ", [], MondialRelayPickupPoint::DOMAIN_NAME),
                    'data'      => MondialRelayPickupPointFreeshippingQuery::create()->findOneById(1)->getFreeshippingFrom(),
                    'scale'     => 2,
                ]
            )

        ;
    }

    /**
     * @return string the name of you form. This name must be unique
     */
    public static function getName()
    {
        return "mondialrelaypickuppoint_freeshipping_form";
    }

}