<?php

namespace App\Form;

use App\Entity\User;
use Carbon\CarbonPeriod;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateSelectorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $months = [
            'January' => 1,
            'February' => 2,
            'March' => 3,
            'April' => 4,
            'May' => 5,
            'June' => 6,
            'July' => 7,
            'August' => 8,
            'September' => 9,
            'October' => 10,
            'November' => 11,
            'December' => 12
        ];

        $currentYear = intval(date('Y'));
        $years = array_combine(
                        range(
                            strval($currentYear - 10),
                            strval($currentYear)),
                        range(
                            $currentYear - 10,
                            $currentYear
                        )
        );

        $builder
            ->add('month', ChoiceType::class, [
                'choices' => $months
            ])
            ->add('year', ChoiceType::class, [
                'choices' => $years
            ])
            ->add('select', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'property_path' => 'date',
        ]);
    }
}