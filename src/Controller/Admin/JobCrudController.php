<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Job;
use App\Enum\EmploymentType;
use App\Form\QuillEditorType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
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
        yield IdField::new('id')
            ->hideOnForm();
        yield TextField::new('title');
        yield TextField::new('employer');
        yield TextField::new('location');
        yield ChoiceField::new('employmentType')
            ->setFormTypeOption('choice_label', fn (EmploymentType $employmentType) => $employmentType->value);
        yield DateField::new('startDate');
        yield DateField::new('endDate');
        yield TextareaField::new('description')
            ->setFormType(QuillEditorType::class)
            ->setFormTypeOptions([
                'upload_url' => $this->generateUrl('admin_upload_image'),
            ])
            ->formatValue(fn ($value) => $value)
            ->hideOnIndex();
    }
}
