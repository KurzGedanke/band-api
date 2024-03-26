<?php

namespace App\Controller\Admin;

use App\Entity\TimeSlot;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class TimeSlotCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TimeSlot::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            DateTimeField::new('startTime'),
            DateTimeField::new('endTime'),
            AssociationField::new('stage'),
            AssociationField::new('band'),
        ];
    }
}
