<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\NotificationModel;

class NotificationController extends BaseController
{
    protected $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
    }

    public function getAllNotifications()
    {
        $page     = (int) ($this->request->getVar('page') ?? 1);
        $perPage = 10;

        $types = $this->getRoleNotificationTypes();
        $query = $this->notificationModel;
        if (!empty($types)) {
            $query = $query->whereIn('type', $types);
        }

        $notifications = $query->orderBy('created_at', 'DESC')
            ->paginate($perPage, 'default', $page);

        $pager = [
            'currentPage' => $this->notificationModel->pager->getCurrentPage('default'),
            'totalPages'  => $this->notificationModel->pager->getPageCount('default'),
            'limit'       => $perPage
        ];

        $data = [
            'data'  => $notifications,
            'pager' => $pager
        ];

        return view('notifications/v_index', $data);
    }

    public function getUnreadNotifications()
    {
        $types = $this->getRoleNotificationTypes();
        $notifications = $this->notificationModel->getNotifications(true, 10, $types);
        $unreadCount  = $this->notificationModel->countUnread($types);

        return $this->response->setJSON([
            'status' => true,
            'count'  => $unreadCount,
            'data'   => $notifications
        ]);
    }

    public function markAllRead()
    {
        $types = $this->getRoleNotificationTypes();
        $query = $this->notificationModel->where('is_read', 0);
        if (!empty($types)) {
            $query = $query->whereIn('type', $types);
        }
        $query->set(['is_read' => 1])->update();

        return $this->response->setJSON([
            'status' => true
        ]);
    }

    public function markRead($id)
    {
        $this->notificationModel
            ->update($id, ['is_read' => 1]);

        return $this->response->setJSON(['status' => true]);
    }

    private function getRoleNotificationTypes(): array
    {
        $roleName = session('role_name');
        if ($roleName === 'cashier') {
            return ['new_order', 'cancel_order', 'refund_order', 'order'];
        }
        if ($roleName === 'admin') {
            return ['stock', 'low_stock', 'stock_empty'];
        }
        return [];
    }
}
