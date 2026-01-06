<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuillEditorType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'upload_url' => '/admin/upload-image',
            'attr'       => [],
        ]);

        $resolver->setAllowedTypes('upload_url', 'string');
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['attr'] = array_merge($view->vars['attr'], [
            'data-controller'                    => 'quill-editor',
            'data-quill-editor-upload-url-value' => $options['upload_url'],
            'class'                              => ($view->vars['attr']['class'] ?? '').' quill-editor-textarea',
            'style'                              => 'display: none;',
        ]);
    }

    #[\Override]
    public function getParent(): string
    {
        return TextareaType::class;
    }
}
