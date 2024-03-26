<?php

namespace App\Controller\Admin;

use App\Entity\Band;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

class BandCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Band::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            TextField::new('genre'),
            ImageField::new('image')
                ->setRequired(false)
                ->setUploadDir('public/images/band/images')
                ->setBasePath('/images/band/images'),
            ImageField::new('logo')
                ->setRequired(false)
                ->setUploadDir('public/images/band/logos')
                ->setBasePath('/images/band/logos'),
            TextField::new('instagram'),
            TextField::new('spotify'),
            TextField::new('apple_music'),
            TextField::new('bandcamp'),
            TextEditorField::new('description'),
            ];
    }
}
