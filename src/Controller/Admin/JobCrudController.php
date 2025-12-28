<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Job;
use App\Enum\EmploymentType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class JobCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Job::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('Job')
            ->setEntityLabelInPlural('Jobs')
            ->setDefaultSort([
                'endDate'   => 'DESC',
                'startDate' => 'DESC',
            ]);
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('title'),
            TextField::new('employer'),
            TextField::new('location'),
            ChoiceField::new('employmentType')
                ->setFormTypeOption('choice_label', fn (EmploymentType $employmentType) => $employmentType->value),
            TextEditorField::new('description')
                ->hideOnIndex()
                ->formatValue(fn ($value) => $value),
            DateField::new('startDate'),
            DateField::new('endDate'),
        ];
    }
}
