<?php

return [
    'groups' => [
        [
            'label' => 'Đơn hàng',
            'permissions' => [
                ['key' => 'orders.view', 'label' => 'Xem đơn hàng và thông tin giao hàng'],
                ['key' => 'orders.status.update', 'label' => 'Cập nhật trạng thái đơn theo quy trình'],
                ['key' => 'orders.shipping.update', 'label' => 'Cập nhật hãng vận chuyển và mã vận đơn'],
            ],
        ],
        [
            'label' => 'Kho hàng',
            'permissions' => [
                ['key' => 'inventory.view', 'label' => 'Xem tồn kho (chỉ đọc)'],
                ['key' => 'inventory.update', 'label' => 'Điều chỉnh số tồn và vị trí sách'],
                ['key' => 'inventory.imports.view', 'label' => 'Xem phiếu nhập kho'],
                ['key' => 'inventory.import', 'label' => 'Tạo phiếu và nhập hàng'],
            ],
        ],
        [
            'label' => 'Thanh toán và tài chính',
            'permissions' => [
                ['key' => 'payments.view', 'label' => 'Xem giao dịch và thông tin thanh toán'],
                ['key' => 'payments.confirm', 'label' => 'Xác nhận chuyển khoản'],
                ['key' => 'reports.revenue.view', 'label' => 'Xem báo cáo doanh thu'],
            ],
        ],
        [
            'label' => 'Chăm sóc khách hàng',
            'permissions' => [
                ['key' => 'chat.view', 'label' => 'Xem hội thoại hỗ trợ'],
                ['key' => 'chat.reply', 'label' => 'Trả lời khách hàng'],
                ['key' => 'reviews.view', 'label' => 'Xem đánh giá'],
                ['key' => 'reviews.moderate', 'label' => 'Duyệt hoặc ẩn đánh giá'],
                ['key' => 'reviews.delete', 'label' => 'Xóa đánh giá'],
            ],
        ],
        [
            'label' => 'Danh mục và khuyến mãi',
            'permissions' => [
                ['key' => 'catalog.manage', 'label' => 'Thêm, sửa, xóa sách và danh mục'],
                ['key' => 'discounts.manage', 'label' => 'Quản lý mã giảm giá'],
            ],
        ],
    ],
    'role_defaults' => [
        'cskh' => [
            'orders.view',
            'orders.status.update',
            'inventory.view',
            'chat.view',
            'chat.reply',
            'reviews.view',
            'reviews.moderate',
            'reviews.delete',
        ],
        'nhan_vien_kho' => [
            'orders.view',
            'orders.shipping.update',
            'inventory.view',
            'inventory.update',
            'inventory.imports.view',
            'inventory.import',
        ],
        'ke_toan' => [
            'orders.view',
            'payments.view',
            'payments.confirm',
            'reports.revenue.view',
        ],
    ],
];
