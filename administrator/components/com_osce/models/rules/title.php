<?php
namespace Joomla\CMS\Form\Rule;
jimport('joomla.form.formrule');


use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;

class JFormRuleTitle extends JFormRule
{
	public function title(SimpleXMLElement $element, $value, $group = null, JRegistry $input = null, JForm $form = null)
    {
        if ($this->title == "books"){
            return false;
        }
    	return true;
    }
}