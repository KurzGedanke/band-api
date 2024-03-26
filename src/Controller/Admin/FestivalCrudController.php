<?php

namespace App\Controller\Admin;

use App\Entity\Festival;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class FestivalCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Festival::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            DateField::new('startDate'),
            DateField::new('endDate'),
            AssociationField::new('bands'),
            AssociationField::new('stages'),
        ];
    }
}
