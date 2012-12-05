<?php

namespace Album\Controller;

use Zend\Mvc\Controller\AbstractActionController, Zend\View\Model\ViewModel, Album\Form\AlbumForm, Doctrine\ORM\EntityManager, Album\Entity\Album;

class AlbumController extends AbstractActionController {
	/**
	 *
	 * @var Doctrine\ORM\EntityManager
	 */
	protected $em;
	public function setEntityManager(EntityManager $em) {
		$this->em = $em;
	}
	public function getEntityManager() {
		if (null === $this->em) {
			$this->em = $this->getServiceLocator ()->get ( 'Doctrine\ORM\EntityManager' );
		}
		return $this->em;
	}
	public function indexAction() {
		var_dump($this->zfcUserAuthentication()->hasIdentity());
		var_dump($this->zfcUserAuthentication()->getIdentity());
		die;
		return new ViewModel ( array (
				'albums' => $this->getEntityManager ()->getRepository ( 'Album\Entity\Album' )->findAll () 
		) );
	}
	public function addAction() {
		$form = new AlbumForm ();
		$form->get ( 'submit' )->setAttribute ( 'label', 'Add' );
		
		$request = $this->getRequest ();
		if ($request->isPost ()) {
			$album = new Album ();
			
			$form->setInputFilter ( $album->getInputFilter () );
			$form->setData ( $request->getPost () );
			if ($form->isValid ()) {
				$fileBank = $this->getServiceLocator()->get('FileBank');
				$file = $this->params ()->fromFiles ( 'image' );
				$fileBank->save($file,array("tirth"));
				$album->populate ( $form->getData () );
				$this->getEntityManager ()->persist ( $album );
				$this->getEntityManager ()->flush ();
				
				// Redirect to list of albums
				return $this->redirect ()->toRoute ( 'album' );
			}
		}
		
		return array (
				'form' => $form 
		);
	}
	public function editAction() {
		$id = ( int ) $this->getEvent ()->getRouteMatch ()->getParam ( 'id' );
		if (! $id) {
			return $this->redirect ()->toRoute ( 'album', array (
					'action' => 'add' 
			) );
		}
		$album = $this->getEntityManager ()->find ( 'Album\Entity\Album', $id );
		
		$form = new AlbumForm ();
		$form->setBindOnValidate ( false );
		$form->bind ( $album );
		$form->get ( 'submit' )->setAttribute ( 'label', 'Edit' );
		
		$request = $this->getRequest ();
		if ($request->isPost ()) {
			$form->setData ( $request->getPost () );
			if ($form->isValid ()) {
				$form->bindValues ();
				$this->getEntityManager ()->flush ();
				
				// Redirect to list of albums
				return $this->redirect ()->toRoute ( 'album' );
			}
		}
		
		return array (
				'id' => $id,
				'form' => $form 
		);
	}
	public function deleteAction() {
		$id = ( int ) $this->getEvent ()->getRouteMatch ()->getParam ( 'id' );
		if (! $id) {
			return $this->redirect ()->toRoute ( 'album' );
		}
		$request = $this->getRequest ();
		if ($request->isPost ()) {
			$del = $request->getPost ()->get ( 'del', 'No' );
			if ($del == 'Yes') {
				$id = ( int ) $request->getPost ()->get ( 'id' );
				$album = $this->getEntityManager ()->find ( 'Album\Entity\Album', $id );
				if ($album) {
					$this->getEntityManager ()->remove ( $album );
					$this->getEntityManager ()->flush ();
				}
			}
			
			// Redirect to list of albums
			return $this->redirect ()->toRoute ( 'album', array (
					'controller' => 'album',
					'action' => 'index' 
			) );
		}
		return array (
				'id' => $id,
				'album' => $this->getEntityManager ()->find ( 'Album\Entity\Album', $id ) 
		);
	}
}