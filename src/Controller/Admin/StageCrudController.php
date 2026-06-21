<?php

namespace App\Controller\Admin;

use App\Entity\Stage;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class StageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Stage::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            TextField::new('slug')
                ->setFormTypeOption('disabled', true)
                ->setHelp('Auto-generated from the name; used in API URLs.')
                ->hideWhenCreating(),
            TextField::new('location'),
            AssociationField::new('timeSlots'),
            AssociationField::new('festival'),
            ];
    }
}
