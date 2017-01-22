<?php

namespace BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use BlogBundle\Entity\Users;
use BlogBundle\Modals\Login;

class DefaultController extends Controller
{

 public function indexAction(Request $request) {
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('BlogBundle:Users');

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


        if ($request->getMethod() == 'POST') {
            $session->clear();
            $username = $request->get('username');
            $password = sha1($request->get('password'));
            $remember = $request->get('remember');
            $user = $repository->findOneBy(array('username' => $username, 'password' => $password));
            if ($user) {
                    $login = new Login();
                    $login->setUsername($username);
                    $login->setPassword($password);
                    $session->set('login', $login);
              return $this->redirectToRoute('blog_list');
               } else {
                return $this->render('BlogBundle:Default:login.html.twig', array(
                    'articles'=>$articles,
                    'total_pages'=>$total_pages,
                    'current_page'=>$page,
                    'name' => 'Login Error'));
            }
        } else {
            if ($session->has('login')) {
                $login = $session->get('login');
                $username = $login->getUsername();
                $password = $login->getPassword();
                $user = $repository->findOneBy(array('username' => $username, 'password' => $password));
                if ($user) {
                
                 return $this->redirectToRoute('blog_list');
                }
            }
            return $this->render('BlogBundle:Default:login.html.twig',array(
                'articles'=>$articles,
                'total_pages'=>$total_pages,
                'current_page'=>$page
                ));
        }
    }
    
public function logoutAction(Request $request) {
        $session = $request->getSession();
        $session->clear();
         $articles = $this->getDoctrine()
                    ->getRepository('BlogBundle:Articles')
                    ->findAll();
         return $this->redirectToRoute('blog_homepage');
        
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
