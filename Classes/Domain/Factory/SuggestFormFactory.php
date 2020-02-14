<?php
declare(strict_types = 1);

/*
 * This file is part of the package evoweb\sessionplaner.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Evoweb\Sessionplaner\Domain\Factory;

use Evoweb\Sessionplaner\Domain\Finisher\SuggestFormFinisher;
use Evoweb\Sessionplaner\Enum\SessionLevelEnum;
use Evoweb\Sessionplaner\Enum\SessionTypeEnum;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Validation\Validator\EmailAddressValidator;
use TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator;
use TYPO3\CMS\Extbase\Validation\Validator\StringLengthValidator;
use TYPO3\CMS\Form\Domain\Configuration\ConfigurationService;
use TYPO3\CMS\Form\Domain\Factory\AbstractFormFactory;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class SuggestFormFactory extends AbstractFormFactory
{
    /**
     * @param array $configuration
     * @param string $prototypeName
     * @return FormDefinition
     */
    public function build(array $configuration, string $prototypeName = null): FormDefinition
    {
        $prototypeName = 'standard';
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $formConfigurationService = $objectManager->get(ConfigurationService::class);
        $prototypeConfiguration = $formConfigurationService->getPrototypeConfiguration($prototypeName);

        $configurationManager = $objectManager->get(ConfigurationManagerInterface::class);
        $settings = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'sessionplaner');

        $form = $objectManager->get(FormDefinition::class, 'suggest', $prototypeConfiguration);
        $form->setRenderingOption('controllerAction', 'form');
        $form->setRenderingOption('submitButtonLabel', LocalizationUtility::translate('form.submit', 'sessionplaner'));
        $page = $form->createPage('suggestform');

        // Personal Information
        $personalinformation = $page->createElement('personalinformation', 'Fieldset');
        $personalinformation->setLabel(LocalizationUtility::translate('label.personalinformation', 'sessionplaner'));

        $fullnameField = $personalinformation->createElement('fullname', 'Text');
        $fullnameField->setLabel(LocalizationUtility::translate('form.fullname', 'sessionplaner'));
        $fullnameField->setProperty('elementDescription', LocalizationUtility::translate('form.fullname.description', 'sessionplaner'));
        $fullnameField->addValidator($objectManager->get(NotEmptyValidator::class));

        $emailField = $personalinformation->createElement('email', 'Text');
        $emailField->setLabel(LocalizationUtility::translate('form.email', 'sessionplaner'));
        $emailField->setProperty('elementDescription', LocalizationUtility::translate('form.email.description', 'sessionplaner'));
        $emailField->addValidator($objectManager->get(NotEmptyValidator::class));
        $emailField->addValidator($objectManager->get(EmailAddressValidator::class));

        $twitterField = $personalinformation->createElement('twitter', 'Text');
        $twitterField->setLabel(LocalizationUtility::translate('form.twitter', 'sessionplaner'));
        $twitterField->setProperty('elementDescription', LocalizationUtility::translate('form.twitter.description', 'sessionplaner'));

        // Session Information
        $sessioninformation = $page->createElement('sessioninformation', 'Fieldset');
        $sessioninformation->setLabel(LocalizationUtility::translate('label.sessioninformation', 'sessionplaner'));

        $typeField = $sessioninformation->createElement('type', 'SingleSelect');
        $typeField->setLabel(LocalizationUtility::translate('form.type', 'sessionplaner'));
        $typeField->setProperty('elementDescription', LocalizationUtility::translate('form.type.description', 'sessionplaner'));
        $typeField->addValidator($objectManager->get(NotEmptyValidator::class));
        $typeFieldOptions = SessionTypeEnum::getOptions();
        foreach ($typeFieldOptions as $typeFieldOptionKey => $typeFieldOptionValue) {
            $typeFieldOptions[$typeFieldOptionKey] = LocalizationUtility::translate($typeFieldOptionValue);
        }
        $typeField->setProperty('prependOptionLabel', ' ');
        $typeField->setProperty('options', $typeFieldOptions);

        $titleField = $sessioninformation->createElement('title', 'Text');
        $titleField->setLabel(LocalizationUtility::translate('form.title', 'sessionplaner'));
        $titleField->setProperty('elementDescription', LocalizationUtility::translate('form.title.description', 'sessionplaner'));
        $titleField->addValidator($objectManager->get(NotEmptyValidator::class));

        $descriptionField = $sessioninformation->createElement('description', 'Textarea');
        $descriptionField->setLabel(LocalizationUtility::translate('form.description', 'sessionplaner'));
        $descriptionField->setProperty('elementDescription', LocalizationUtility::translate('form.description.description', 'sessionplaner'));
        $descriptionField->addValidator($objectManager->get(NotEmptyValidator::class));
        $descriptionField->addValidator($objectManager->get(StringLengthValidator::class, ['minimum' => 5]));

        $lengthField = $sessioninformation->createElement('estimatedlength', 'SingleSelect');
        $lengthField->setLabel(LocalizationUtility::translate('form.estimatedlength', 'sessionplaner'));
        $lengthField->setProperty('elementDescription', LocalizationUtility::translate('form.estimatedlength.description', 'sessionplaner'));
        $lengthField->setProperty('options', [
            '45 Minutes' => '45 Minutes',
            '90 Minutes' => '90 Minutes'
        ]);

        $levelField = $sessioninformation->createElement('level', 'SingleSelect');
        $levelField->setLabel(LocalizationUtility::translate('form.level', 'sessionplaner'));
        $levelField->setProperty('elementDescription', LocalizationUtility::translate('form.level.description', 'sessionplaner'));
        $levelField->addValidator($objectManager->get(NotEmptyValidator::class));
        $levelFieldOptions = SessionLevelEnum::getOptions();
        foreach ($levelFieldOptions as $levelFieldOptionKey => $levelFieldOptionValue) {
            $levelFieldOptions[$levelFieldOptionKey] = LocalizationUtility::translate($levelFieldOptionValue);
        }
        $levelField->setProperty('prependOptionLabel', ' ');
        $levelField->setProperty('options', $levelFieldOptions);

        $explanationText = $page->createElement('headline', 'StaticText');
        $explanationText->setProperty('text', LocalizationUtility::translate('label.required.field', 'sessionplaner') . ' ' . LocalizationUtility::translate('label.required.field.explanation', 'sessionplaner'));

        // Finisher
        $commentFinisher = $objectManager->get(SuggestFormFinisher::class);
        $form->addFinisher($commentFinisher);

        if ($settings['suggest']['notification']['enable'] &&
            !empty($settings['suggest']['notification']['subject']) &&
            !empty($settings['suggest']['notification']['recipientAddress']) &&
            !empty($settings['suggest']['notification']['recipientName']) &&
            !empty($settings['suggest']['notification']['senderAddress']) &&
            !empty($settings['suggest']['notification']['senderName'])
        ) {
            $form->createFinisher('EmailToReceiver', [
                'subject' => $settings['suggest']['notification']['subject'] ?? '',
                'recipientAddress' => $settings['suggest']['notification']['recipientAddress'] ?? '',
                'recipientName' => $settings['suggest']['notification']['recipientName'] ?? '',
                'senderAddress' => $settings['suggest']['notification']['senderAddress'] ?? '',
                'senderName' => $settings['suggest']['notification']['senderName'] ?? '',
                'carbonCopyAddress' => $settings['suggest']['notification']['carbonCopyAddress'] ?? '',
                'blindCarbonCopyAddress' => $settings['suggest']['notification']['blindCarbonCopyAddress'] ?? '',
                'format' => 'html'
            ]);
        }

        if ($settings['suggest']['confirmation']['pageUid']) {
            $form->createFinisher('Redirect', [
                'pageUid' => (int) $settings['suggest']['confirmation']['pageUid'],
            ]);
        } else {
            $message = $settings['suggest']['confirmation']['message'] ?? 'LLL:EXT:sessionplaner/Resources/Private/Language/locallang.xlf:form.suggest.confirmation';
            $form->createFinisher('Confirmation', [
                'message' => LocalizationUtility::translate($message),
            ]);
        }

        $this->triggerFormBuildingFinished($form);
        return $form;
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController(): ?TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
