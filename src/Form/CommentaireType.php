<?php

namespace App\Form;

use App\Entity\Commentaire;
use App\Repository\CommentaireRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CommentaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $article = $options['article'];

        $builder
            ->add('contenu', TextareaType::class, [
                'label' => 'Contenu du commentaire',
                'attr' => [
                    'rows' => 5,
                ],
            ])
            ->add('commentaire_parent', EntityType::class, [
                'class' => Commentaire::class,
                'choice_label' => function (Commentaire $commentaire) {
                    return substr($commentaire->getContenu(), 0, 50) . '...'; // Limitez la longueur du contenu affiché
                },
                'required' => false,
                'placeholder' => 'Sélectionnez un commentaire parent',
                'query_builder' => function (CommentaireRepository $repo) use ($article) {
                    return $repo->createQueryBuilder('c')
                        ->where('c.article = :article')
                       // ->andWhere('c.commentaireParent IS NULL') // Assurez-vous de ne récupérer que les commentaires parents
                        ->setParameter('article', $article)
                        ->orderBy('c.date_creation', 'ASC'); // Optionnel : ordonner par date de création
                },
            ])
            ->add('Ajouter', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commentaire::class,
            'article' => null, // Vous devez passer l'article lors de la création du formulaire
        ]);
    }
}
