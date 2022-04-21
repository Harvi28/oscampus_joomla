<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class GeneratequizModelForm extends JModelAdmin
{
    public function getTable($type = 'Generatequiz', $prefix = 'GenerateTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm(
            'com_osce.form',
            'add-form',
            array(
                'control' => 'jform',
                'load_data' => $loadData
            )
        );

        if (empty($form))
        {
            $errors = $this->getErrors();
            throw new Exception(implode("\n", $errors), 500);
        }

        return $form;
    }

}
