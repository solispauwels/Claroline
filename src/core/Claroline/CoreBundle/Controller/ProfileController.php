<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Event\Log\LogUserUpdateEvent;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\ProfileType;
use Claroline\CoreBundle\Manager\NodeManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Controller of the user profile.
 */
class ProfileController extends Controller
{
    private $userManager;
    private $roleManager;
    private $eventDispatcher;
    private $security;

    /**
     * @DI\InjectParams({
     *     "userManager"        = @DI\Inject("claroline.manager.user_manager"),
     *     "roleManager"        = @DI\Inject("claroline.manager.role_manager"),
     *     "node"               = @DI\Inject("claroline.manager.node_manager"),
     *     "eventDispatcher"    = @DI\Inject("claroline.event.event_dispatcher"),
     *     "security"           = @DI\Inject("security.context")
     * })
     */
    public function __construct(
        UserManager $userManager,
        RoleManager $roleManager,
        NodeManager $node,
        StrictDispatcher $eventDispatcher,
        SecurityContextInterface $security
    )
    {
        $this->userManager = $userManager;
        $this->roleManager = $roleManager;
        $this->node = $node;
        $this->eventDispatcher = $eventDispatcher;
        $this->security = $security;
    }

    private function isInRoles($role, $roles)
    {
        foreach ($roles as $current) {
            if ($role->getId() == $current->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @EXT\Route(
     *     "/form",
     *     name="claro_profile_form"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Profile:profileForm.html.twig")
     *
     * Displays an editable form of the current user's profile.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function formAction()
    {
        $user = $this->security->getToken()->getUser();
        $roles = $this->roleManager->getPlatformRoles($user);
        $form = $this->createForm(new ProfileType($roles), $user);

        return array('profile_form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/update",
     *     name="claro_profile_update"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Profile:profileForm.html.twig")
     *
     * Updates the user's profile and redirects to the profile form.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction()
    {
        $request = $this->get('request');
        $user = $this->security->getToken()->getUser();
        $roles = $this->roleManager->getPlatformRoles($user);

        $form = $this->get('form.factory')->create(new ProfileType($roles), $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $unitOfWork = $em->getUnitOfWork();
            $unitOfWork->computeChangeSets();
            $changeSet = $unitOfWork->getEntityChangeSet($user);
            $newRoles = $form->get('platformRoles')->getData();

            $this->roleManager->resetRoles($user);
            $this->roleManager->associateRoles($user, $newRoles);
            $this->security->getToken()->setUser($user);

            $newRoles = $this->roleManager->getPlatformRoles($user);

            $rolesChangeSet = array();
            //Detect added
            foreach ($newRoles as $role) {
                if (!$this->isInRoles($role, $roles)) {
                    $rolesChangeSet[$role->getTranslationKey()] = array(false, true);
                }
            }
            //Detect removed
            foreach ($roles as $role) {
                if (!$this->isInRoles($role, $newRoles)) {
                    $rolesChangeSet[$role->getTranslationKey()] = array(true, false);
                }
            }
            if (count($rolesChangeSet) > 0) {
                $changeSet['roles'] = $rolesChangeSet;
            }

            $this->eventDispatcher->dispatch(
                'log',
                'Log\LogUserUpdateEvent',
                array($user, $changeSet)
            );

            return $this->redirect($this->generateUrl('claro_profile_form'));
        }

        return array('profile_form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/view/{userId}",
     *     name="claro_profile_view"
     * )
     * @EXT\ParamConverter(
     *      "user",
     *      class="ClarolineCoreBundle:User",
     *      options={"id" = "userId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Profile:profile.html.twig")
     *
     * Displays the public profile of an user.
     *
     * @param integer $userId The id of the user we want to see the profile
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(User $user)
    {
        /*$comment = $this->node->updateType('comment');
        $content = $this->node->updateType('content');
        $a = $this->node->updateNode('title1', 'value1', $content);
        $b = $this->node->updateNode('title2', 'value2', $content);
        $c = $this->node->updateNode('title3', 'value3', $content);
        $d = $this->node->updateNode('title4', 'value4', $content);
        $e = $this->node->updateNode('title5', 'value5', $content);
        $f = $this->node->updateNode('title6', 'value6', $content);
        $g = $this->node->updateNode('title7', 'value7', $content);

        $this->node->updateLink($a, $comment, $b);
        $this->node->updateLink($c, $comment, $b);
        $this->node->updateLink($d, $comment, $b);
        $this->node->updateLink($e, $comment, $b);
        $this->node->updateLink($f, $comment, $b);
        $this->node->updateLink($g, $comment, $b);

        //$a = $this->node->updateNode('tessqdfqsd', 'hola qsdf qsd ', 'content');
        //$this->node->updateLink($a, 'comment', $this->node->getNode(4));*/

        print_r($this->node->get('content', 'comment', 'content', 23)->getQuery()->getArrayResult());
        //print_r($this->node->getAll(23)->getQuery()->getArrayResult());
        //print_r($this->node->query('content comment content'));

        return array('user' => $user);
    }
}
