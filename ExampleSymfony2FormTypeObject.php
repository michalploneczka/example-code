<?php

namespace MichalPloneczka\Bundle\CatalogBundle\Form;

use MichalPloneczka\Bundle\CatalogBundle\Form\DataTransformer\NewAttributeToNumberTransformer;
use MichalPloneczka\Bundle\CatalogBundle\Form\DataTransformer\ProductToNumberTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * The definition of product attributes values form
 *
 * Class ProductAttributeValueType
 * @package MichalPloneczka\Bundle\CatalogBundle\Form
 */
class ProductAttributeValueType extends AbstractType
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $entityManager;

	/** @var  \MichalPloneczka\Bundle\CatalogBundle\Entity\Attribute */
	private $attribute;


	public function __construct($em, $attribute=null) {
		$this->entityManager = $em;
		$this->attribute = $attribute;
	}


	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$entityManager = $this->entityManager;
		$attributeTransformer = new NewAttributeToNumberTransformer($entityManager);
		$productTransformer = new ProductToNumberTransformer($entityManager);

		$unitFormModifier = function(FormInterface $form, $unitGroup) {
			$choices = array();
			foreach ($unitGroup->getUnits() as $item) {
				$choices[] = $item;
			}

			$form->add('unit', null, array(
					'label'=>'Jednostka',
					'required'=>false,
					'choices' => $choices,
					'attr'=>array('class'=>'form-control')
				));
		};

		$builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($unitFormModifier) {


				$attribute = $event->getData()->getNewAttribute();

				$builder = $event->getForm();

				if ($attribute) {

					$type = $attribute->getTypeId();
					if ($type && $type->getFormField()=='numeric') {
						$builder->add('attribute_value', null, array('label'=>$attribute->getName(), 'required'=>false, 'attr'=>array('class'=>'form-control')));

						if ($attribute->getUnitGroup()) {
							$unitFormModifier($builder, $attribute->getUnitGroup());
						}

					} elseif ($type && $type->getFormField()=='text') {
						$builder->add('attribute_value', 'textarea', array('label'=>$attribute->getName(), 'required'=>false, 'attr'=>array('class'=>'form-control')));

					} elseif($type && $type->getFormField()=='dictionary') {
						$options = array();
						foreach ($attribute->getOptions() as $option) {
							$options[$option->getOptionValue()] = $option->getName();
						}
						$builder->add('attribute_value', 'choice', array(
								'label'=>$attribute->getName(),
								'choices'=>$options,
								'required'=>false,
								'attr'=>array('class'=>'form-control')
							));
					} else {
						$builder->add('attribute_value', 'textarea', array('label'=>$attribute->getName(), 'required'=>false, 'attr'=>array('class'=>'form-control')));
					}
				} else {
					$builder->add('attribute_value', 'textarea', array('label'=>$event->getData()->getAttribute(), 'required'=>false, 'attr'=>array('class'=>'form-control')));
				}
			});

		$builder
			->add($builder->create('newAttribute', 'hidden')
					->addModelTransformer($attributeTransformer))
		;
	}

	/**
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
				'data_class' => 'MichalPloneczka\Bundle\CatalogBundle\Entity\AttributeValue'
			));

	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'MichalPloneczka_bundle_catalogbundle_attributevalue';
	}
}
