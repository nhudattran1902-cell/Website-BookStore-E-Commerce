<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sach;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminActionLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'action' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:150'],
        ]);

        $logs = DB::table('admin_action_logs')
            ->leftJoin('nguoi_dung', 'nguoi_dung.id', '=', 'admin_action_logs.id_nguoi_thuc_hien')
            ->select([
                'admin_action_logs.*',
                'nguoi_dung.ho_ten as actor_name',
                'nguoi_dung.email as actor_email',
            ])
            ->when($filters['action'] ?? null, fn ($query, string $action) => $query->where('hanh_dong', $action))
            ->when($filters['email'] ?? null, fn ($query, string $email) => $query->where('nguoi_dung.email', $email))
            ->orderByDesc('admin_action_logs.id')
            ->paginate(30)
            ->withQueryString();

        $bookIds = $logs->getCollection()
            ->map(fn (object $log): ?int => $this->inventoryBookId($log))
            ->filter()
            ->unique()
            ->values();
        $bookTitles = $bookIds->isEmpty()
            ? collect()
            : Sach::query()->whereIn('id', $bookIds)->pluck('tieu_de', 'id');

        $logs->getCollection()->transform(function (object $log) use ($bookTitles): object {
            $subjectLabel = $this->subjectLabel($log->doi_tuong);
            $subjectId = $log->doi_tuong_id;

            if (($bookId = $this->inventoryBookId($log)) !== null) {
                $bookTitle = $bookTitles->get($bookId, 'Không tìm thấy sách');
                $log->doi_tuong_display = $bookTitle.' (#'.$bookId.')';
            } else {
                $log->doi_tuong_display = $subjectLabel.($subjectId !== null && $subjectId !== '' ? ' #'.$subjectId : '');
            }

            $log->hanh_dong_label = $this->actionLabel($log->hanh_dong);
            $log->detail_lines = $this->formatDetails($log->du_lieu, $log->doi_tuong === 'kho_hang');

            return $log;
        });

        return view('admin.action-logs.index', [
            'logs' => $logs,
            'actions' => $this->actionLabels(),
        ]);
    }

    private function inventoryBookId(object $log): ?int
    {
        if ($log->doi_tuong !== 'kho_hang') {
            return null;
        }

        $details = json_decode($log->du_lieu ?? '', true);
        $bookId = is_array($details) ? ($details['id_sach'] ?? null) : null;

        return is_numeric($bookId) ? (int) $bookId : null;
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    private function formatDetails(?string $encodedDetails, bool $isInventoryLog = false): array
    {
        if ($encodedDetails === null || $encodedDetails === '') {
            return [];
        }

        $details = json_decode($encodedDetails, true);

        if (! is_array($details)) {
            return [];
        }

        $lines = [];
        $before = is_array($details['before'] ?? null) ? $details['before'] : [];
        $after = is_array($details['after'] ?? null) ? $details['after'] : [];

        foreach (array_unique([...array_keys($before), ...array_keys($after)]) as $key) {
            $previousValue = $before[$key] ?? null;
            $currentValue = $after[$key] ?? null;

            if ($previousValue === $currentValue) {
                continue;
            }

            $lines[] = [
                'label' => $this->detailLabel((string) $key),
                'value' => $this->formatDetailValue($previousValue).' → '.$this->formatDetailValue($currentValue),
            ];
        }

        foreach (array_diff_key($details, ['before' => true, 'after' => true]) as $key => $value) {
            if ($isInventoryLog && $key === 'id_sach') {
                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            $formattedValue = $this->formatDetailValue($value);

            if ($key === 'id_sach' && is_scalar($value)) {
                $formattedValue = '#'.$formattedValue;
            }

            $lines[] = [
                'label' => $this->detailLabel((string) $key),
                'value' => $formattedValue,
            ];
        }

        return $lines;
    }

    private function actionLabel(string $action): string
    {
        return $this->actionLabels()[$action] ?? $action;
    }

    /** @return array<string, string> */
    private function actionLabels(): array
    {
        return [
            'inventory.adjusted' => 'Điều chỉnh tồn kho',
            'inventory.stock_received' => 'Nhập thêm hàng vào kho',
            'inventory.import_receipt.created' => 'Tạo phiếu nhập kho',
            'user.role.updated' => 'Cập nhật vai trò nhân viên',
            'user.permissions.updated' => 'Cập nhật quyền nhân viên',
            'order.status.updated' => 'Cập nhật trạng thái đơn hàng',
            'order.shipping.updated' => 'Cập nhật thông tin vận chuyển',
            'payment.bank_transfer.confirmed' => 'Xác nhận thanh toán chuyển khoản',
        ];
    }

    private function subjectLabel(string $subject): string
    {
        return match ($subject) {
            'kho_hang' => 'Kho hàng',
            'nguoi_dung' => 'Tài khoản',
            'don_hang' => 'Đơn hàng',
            default => $subject,
        };
    }

    private function detailLabel(string $key): string
    {
        return match ($key) {
            'so_luong_ton' => 'Số lượng tồn',
            'so_luong_nhap' => 'Số lượng nhập',
            'id_sach' => 'Mã sách',
            'o_chua' => 'Ô chứa',
            'ke_hang' => 'Kệ hàng',
            'khu_vuc' => 'Khu vực',
            'trang_thai' => 'Trạng thái',
            'ghi_chu' => 'Ghi chú',
            'role' => 'Vai trò',
            'permissions' => 'Quyền',
            'email' => 'Email',
            default => ucfirst(str_replace('_', ' ', $key)),
        };
    }

    private function formatDetailValue(mixed $value): string
    {
        if ($value === null) {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? 'Có' : 'Không';
        }

        if (is_array($value)) {
            if ($value === []) {
                return 'Không có';
            }

            $items = [];

            foreach ($value as $key => $item) {
                $label = is_string($key) ? $this->detailLabel($key).': ' : '';
                $items[] = $label.$this->formatDetailValue($item);
            }

            return implode(', ', $items);
        }

        return (string) $value;
    }
}
