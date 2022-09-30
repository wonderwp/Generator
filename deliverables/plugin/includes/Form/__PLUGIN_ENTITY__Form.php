<?php

namespace __PLUGIN_NS__\Form;

use WonderWp\Plugin\Core\Framework\EntityMapping\EntityAttribute;
use WonderWp\Plugin\Core\Framework\Form\ModelForm;

/**
 * Class that defines the form to use when adding / editing the entity
 */
class __PLUGIN_ENTITY__Form extends ModelForm
{
    /** @inheritdoc */
    public function newField(EntityAttribute $attr)
    {
        $fieldName = $attr->getFieldName();
        $entity    = $this->getModelInstance();

        // Add here particular cases for your different fields
        switch ($fieldName) {
            default:
                $f = parent::newField($attr);
                break;
        }

        return $f;
    }
}
