<?php

/**
 * Render the label of a powermail field instead of his value for select, check and radio
 * For powermail 2.0>=
 *
 * Use it in Partials/PowermailAll/Web.html or Partials/PowermailAll/Mail.html
 * {namespace t3devapi=Tx_T3devapi_ViewHelpers}
 * ...
 * Use
 * <t3devapi:Powermail.CorrectTextAnswer answer="{answer}" />
 *
 * Instead of
 * <f:if condition="{vh:Condition.IsArray(val: '{answer.value}')}">
 * ...
 * </f:if>
 *
 * @package    TYPO3
 * @subpackage Fluid
 * @version
 */
class Tx_T3devapi_ViewHelpers_Powermail_CorrectTextAnswerViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

    public function render(\In2code\Powermail\Domain\Model\Answer $answer = NULL) {

        $textAnswer = '';

        if ($answer) {
            $field = $answer->getField();
            $answerValue = $answer->getValue();
            switch ($field->getType()) {
                case 'select':
                case 'check':
                case 'radio':
                    $possibleAnswers = $field->getModifiedSettings();
                    if (!is_array($answerValue)) {
                        $answerValue = array($answerValue);
                    }
                    $i = 0;
                    foreach ($answerValue as $value) {
                        foreach ($possibleAnswers as $possibleAnswer) {
                            if ($possibleAnswer['value'] == $value)
                                $textAnswer .= $possibleAnswer['label'];
                        }
                        if ($i == (count($possibleAnswers) - 1))
                            $textAnswer .= ', ';
                        $i++;
                    }
                    break;
                default:
                    $textAnswer = $answerValue;
                    break;
            }
        }
        return $textAnswer;

    }
}