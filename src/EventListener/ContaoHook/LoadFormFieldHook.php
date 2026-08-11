<?php

declare(strict_types=1);

/*
 * This file is part of form-field-access-protection-bundle.
 *
 * (c) 2020 Dr. Philip von der Borch <git@vonderborch.com>
 *
 * @license LGPL-3.0-or-later
 */

namespace Philvdb\FormFieldAccessProtectionBundle\EventListener\ContaoHook;


use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Form;
use Contao\FrontendUser;
use Contao\StringUtil;
use Contao\Widget;
use Philvdb\FormFieldAccessProtectionBundle\Form\FormEmpty;

class LoadFormFieldHook
{
    public function __construct(private TokenChecker $tokenChecker)
    {
    }

    #[AsHook('loadFormField')]
    public function __invoke(Widget $objWidget, string $strFormId, array $arrFormData, Form $objForm): Widget
    {
        // Show to guests only
        if ($objWidget->guests && $this->tokenChecker->hasFrontendUser() && !$this->tokenChecker->hasBackendUser() && !$objWidget->protected)
        {
            return new FormEmpty();
        }

        // Protected element
        if (!$this->tokenChecker->hasBackendUser() && $objWidget->protected)
        {
            if (!$this->tokenChecker->hasFrontendUser())
            {
                return new FormEmpty();
            }

            $objFrontendUser = FrontendUser::getInstance();
            $groups = StringUtil::deserialize($objWidget->groups);

            if (!is_array($groups) || count($groups) < 1 || count(array_intersect($groups, $objFrontendUser->groups)) < 1)
            {
                return new FormEmpty();
            }
        }

        return $objWidget;
    }
}
