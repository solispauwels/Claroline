<?php

namespace Claroline\CoreBundle\Library\Widget;

use Claroline\CoreBundle\Entity\Widget\DisplayConfig;

class Manager
{
    private $em;

    public function __construct($em)
    {
        $this->em = $em;
    }

    public function generateWorkspaceDisplayConfig($workspaceId)
    {
        $workspace = $this->em->getRepository('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace')->find($workspaceId);

        $workspaceConfigs = $this->setEntitiesArrayKeysAsIds($this->em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findBy(array('workspace' => $workspace)));
        $adminConfigs = $this->setEntitiesArrayKeysAsIds($this->em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findBy(array('parent' => null)));

        foreach ($workspaceConfigs as $workspaceConfig) {
            if (!$workspaceConfig->getParent()->isLocked()) {
                unset($adminConfigs[$workspaceConfig->getParent()->getId()]);
            } else {
                unset($workspaceConfigs[$workspaceConfig->getId()]);
            }
        }

        $childConfigs = array();

        foreach ($adminConfigs as $adminConfig) {
            $childConfigs[] = $this->generateChild($adminConfig);
        }

        $configs = array_merge($workspaceConfigs, $childConfigs);

        return $configs;
    }

    public function generateDisplayConfig($widgetId, $workspaceId)
    {
        $wsConfig = $this->em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findOneBy(array('workspace' => $workspaceId, 'widget' => $widgetId));
        $adminConfig = $this->em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findOneBy(array('parent' => null, 'widget' => $widgetId));

        if($wsConfig != null){
            if($wsConfig->getParent()->isLocked()){
                return $adminConfig;
            } else {
                return $wsConfig;
            }
        } else {
            return $adminConfig;
        }
    }

    private function setEntitiesArrayKeysAsIds($array)
    {
        $tmpArray = array();
        foreach ($array as $item){
            $tmpArray[$item->getId()] = $item;
        }

        return $tmpArray;
    }

    private function generateChild($config){
        $childConfig = new DisplayConfig();
        $childConfig->setParent($config);
        $childConfig->setVisible($config->isVisible());
        $childConfig->setWidget($config->getWidget());
        $childConfig->setLock($config->isLocked());
        $lvl = $config->getLvl();
        $lvl++;
        $childConfig->setLvl($lvl);

        return $childConfig;
    }


}
