<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Bundle\PropelBundle\Form;

use Propel\Bundle\PropelBundle\Form\PropelTypeGuesser;
use Propel\Bundle\PropelBundle\Form\Type\ModelType;
use Propel\Bundle\PropelBundle\Form\Type\TranslationCollectionType;
use Propel\Bundle\PropelBundle\Form\Type\TranslationType;
use Symfony\Component\Form\AbstractExtension;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Represents the Propel form extension, which loads the Propel functionality.
 *
 * @author Joseph Rouff <rouffj@gmail.com>
 */
class PropelExtension extends AbstractExtension
{
    protected function loadTypes()
    {
        return [
            new ModelType(PropertyAccess::createPropertyAccessor()),
            new TranslationCollectionType(),
            new TranslationType(),
        ];
    }

    protected function loadTypeGuesser()
    {
        return new PropelTypeGuesser();
    }
}
