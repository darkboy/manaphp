<?php
namespace App\Admin\Menu\Controllers;

use App\Admin\Menu\Models\Group;
use App\Admin\Menu\Models\Item;
use ManaPHP\Mvc\Controller;

class GroupController extends Controller
{
    public function indexAction()
    {
        if ($this->request->isAjax()) {
            $groups = Group::criteria()
                ->whereRequest(['group_id'])
                ->orderBy('display_order DESC, group_id ASC')
                ->execute();
            return $this->response->setJsonContent(['code' => 0, 'message' => '', 'data' => ['items' => $groups]]);
        }
    }

    public function listAction()
    {
        if ($this->request->isAjax()) {
            return $this->response->setJsonContent(['code' => 0, 'message' => '', 'data' => Group::findList([], ['group_id' => 'group_name'])]);
        }
    }

    public function createAction()
    {
        if ($this->request->isPost()) {
            try {
                $group_name = $this->request->get('group_name', '*');
                $display_order = $this->request->get('display_order', 'int', 0);
            } catch (\Exception $e) {
                return $this->response->setJsonContent(['code' => 1, 'message' => $e->getMessage()]);
            }

            $group = Group::findFirst(['group_name' => $group_name]);
            if ($group) {
                return $this->response->setJsonContent(['code' => 2, 'message' => 'group is exists']);
            }

            $group = new Group();

            $group->group_name = $group_name;
            $group->display_order = $display_order;
            $group->creator_id = $this->userIdentity->getId();
            $group->creator_name = $this->userIdentity->getName();
            $group->updated_time = $group->created_time = time();

            $group->create();

            return $this->response->setJsonContent(['code' => 0, 'message' => '']);
        }
    }

    public function editAction()
    {
        if ($this->request->isPost()) {
            try {
                $group_id = $this->request->get('group_id', '*|int');
                $group_name = $this->request->get('group_name', '*');
                $display_order = $this->request->get('display_order', 'int', 0);
            } catch (\Exception $e) {
                return $this->response->setJsonContent(['code' => 1, 'message' => $e->getMessage()]);
            }

            $group = Group::findById($group_id);
            if (!$group) {
                return $this->response->setJsonContent(['code' => 2, 'message' => 'group is not exists']);
            }

            if ($group->group_name !== $group_name && Group::exists(['group_name' => $group_name])) {
                return $this->response->setJsonContent(['code' => 3, 'message' => 'group name is exists']);
            }

            $group->group_name = $group_name;
            $group->display_order = $display_order;
            $group->updated_time = time();

            $group->update();

            return $this->response->setJsonContent(['code' => 0, 'message' => '']);
        }
    }

    public function deleteAction()
    {
        if ($this->request->isPost()) {
            try {
                $group_id = $this->request->get('group_id', '*|int');
            } catch (\Exception $e) {
                return $this->response->setJsonContent(['code' => 1, 'message' => $e->getMessage()]);
            }

            $group = Group::findById($group_id);
            if (!$group) {
                return $this->response->setJsonContent(['code' => 2, 'message' => 'group is not exists']);
            }

            if (Item::exists(['group_id' => $group_id])) {
                return $this->response->setJsonContent(['code' => 3, 'message' => 'this group has item']);
            }

            $group->delete();

            return $this->response->setJsonContent(['code' => 0, 'message' => '']);
        }
    }
}