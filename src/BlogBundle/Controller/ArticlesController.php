<?php

namespace BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use BlogBundle\Entity\Articles;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Collection;
class ArticlesController extends Controller
{
     public function listAction(Request $request) {
        $session = $request->getSession();

        if ($session->has('login')) {
            
            $page = $request->get('page');
            $count_per_pages=2;
            $total_count=$this->getTotalArticles();
            $total_pages=ceil($total_count/$count_per_pages);
            if(!is_numeric($page)){
                    $page=1;
            }else{
                $page=floor($page);
            }
            if($total_count <= $count_per_pages){
                    $page=1;
            }
            if($total_count<=$count_per_pages){
                $page=1;
            }
            if(($page*$count_per_pages)>$total_count){
                $page=$total_pages;
            }
            $offset=0;
            if($page > 1){
                $offset= $count_per_pages *($page-1);
            }
            $em = $this->getDoctrine()->getManager();
            $query = $em->createQueryBuilder()
                    ->select('article')
                    ->from('BlogBundle:Articles','article')
                    ->setFirstResult($offset)
                    ->setMaxResults($count_per_pages);
            $finalQuery=$query->getQuery();
            $articles=$finalQuery->getArrayResult();

           return $this->render('BlogBundle:Articles:list.html.twig',array(
                'articles' => $articles,
                'total_pages'=>$total_pages,
                'current_page'=>$page
            ));
        
        }else{

            return $this->render('BlogBundle:Default:login.html.twig');
        }
    }

 public function createAction(Request $request) {
        $session = $request->getSession();
        if ($session->has('login')) {
            $article = new Articles;

            $form = $this->createFormBuilder($article)
                         ->add('name', TextType::class, array('attr'=>array('class'=>'form-control','style'=>'margin-bottom:15px')))
                         ->add('emailAddress', TextType::class, array(
                             'constraints' =>new Email(array('message' => 'Invalid email address')),
                            'attr'=>array('class'=>'form-control','style'=>'margin-bottom:15px')))
                         ->add('text', TextareaType::class, array('attr'=>array('class'=>'form-control','style'=>'margin-bottom:15px ; width: 730px;
                               height: 172px')))
                          ->add('save', SubmitType::class, array('label'=>'Create article' , 'attr'=>array('class'=>'btn btn-primary','style'=>'margin-bottom:15px')))
                         ->getForm();

            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid() ){
                $name = $form['name']->getData();
                $emailAddress = $form['emailAddress']->getData();
                $text = $form['text']->getData();

                $article->setName($name);
                $article->setEmailAddress($emailAddress);
                $article->setText($text);

                $em = $this->getDoctrine()->getManager();
                $em->persist($article);
                $em->flush();
                $this->addFlash('notice','New article has been added');
                 return $this->redirectToRoute('blog_list');
            }

        return $this->render('BlogBundle:Articles:create.html.twig',array(
            'form'=>$form->createView()
            ));
        }else{

           return $this->redirectToRoute('blog_homepage');
        }
    }


 public function editAction(Request $request , $id) {
        $session = $request->getSession();
        if ($session->has('login')) {
             $article = new Articles;

            $article->setName($article->getName());
            $article->setEmailAddress($article->getEmailAddress());
            $article->setText($article->getText());

            $em = $this->getDoctrine()->getManager();
            $article = $em->getRepository('BlogBundle:Articles')
                            ->find($id);
        
            $form = $this->createFormBuilder($article)
                         ->add('name', TextType::class, array('attr'=>array('class'=>'form-control','style'=>'margin-bottom:15px')))
                         ->add('emailAddress', TextType::class, array(
                             'constraints' =>new Email(array('message' => 'Invalid email address')),
                            'attr'=>array('class'=>'form-control','style'=>'margin-bottom:15px')))
                         ->add('text', TextareaType::class, array('attr'=>array('class'=>'form-control','style'=>'margin-bottom:15px ; width: 730px;
                               height: 172px')))
                          ->add('save', SubmitType::class, array('label'=>'Edit article' , 'attr'=>array('class'=>'btn btn-primary','style'=>'margin-bottom:15px')))
                         ->getForm();

            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid() ){
                $name = $form['name']->getData();
                $emailAddress = $form['emailAddress']->getData();
                $text = $form['text']->getData();

                $article->setName($name);
                $article->setEmailAddress($emailAddress);
                $article->setText($text);

                $em->flush();
                $this->addFlash('notice','New article has been edited');
                 return $this->redirectToRoute('blog_list');
            }
         return $this->render('BlogBundle:Articles:edit.html.twig',array(
            'form'=>$form->createView()
            ));
        }else{

            return $this->redirectToRoute('blog_homepage');
        }
    }
      

public function deleteAction(Request $request , $id) {
        $session = $request->getSession();
        if ($session->has('login')) {
            $em = $this->getDoctrine()->getManager();
            $article = $em->getRepository('BlogBundle:Articles')->find($id);
            $em->remove($article);
            $em->flush();
        return $this->redirectToRoute('blog_list');
        }else{

        return $this->redirectToRoute('blog_homepage');
        }
    }
public function getTotalArticles(){
        $em = $this->getDoctrine()->getManager();
        $qb= $em->createQueryBuilder();
                $qb->select('count(article.id)');
                $qb->from('BlogBundle:Articles','article');
                $count = $qb->getQuery()->getSingleScalarResult();
                return $count;
 }   
}