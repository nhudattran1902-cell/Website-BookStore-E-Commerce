<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaGiamGia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DiscountCodeController extends Controller
{
    public function index(): View
    {
        $discountCodes = MaGiamGia::query()
            ->orderByDesc('ngay_tao')
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.discount-codes.index', compact('discountCodes'));
    }

    public function create(): View
    {
        return view('admin.discount-codes.form', ['discountCode' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $validated['da_su_dung'] = 0;

        MaGiamGia::create($validated);

        return redirect()->route('admin.discount-codes.index')->with('success', 'Tạo mã giảm giá thành công.');
    }

    public function edit(MaGiamGia $discountCode): View
    {
        return view('admin.discount-codes.form', compact('discountCode'));
    }

    public function update(Request $request, MaGiamGia $discountCode): RedirectResponse
    {
        $discountCode->update($this->validatedData($request, $discountCode));

        return redirect()->route('admin.discount-codes.index')->with('success', 'Cập nhật mã giảm giá thành công.');
    }

    public function destroy(MaGiamGia $discountCode): RedirectResponse
    {
        if ($discountCode->donHangs()->exists()) {
            return back()->with('error', 'Mã đã được dùng trong đơn hàng. Hãy chuyển trạng thái sang ngừng hoạt động thay vì xóa.');
        }

        $discountCode->delete();

        return redirect()->route('admin.discount-codes.index')->with('success', 'Đã xóa mã giảm giá.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?MaGiamGia $discountCode = null): array
    {
        $request->merge([
            'ma_code' => Str::upper(trim((string) $request->input('ma_code'))),
            'dang_hoat_dong' => $request->boolean('dang_hoat_dong'),
        ]);

        return $request->validate([
            'ma_code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('ma_giam_gia', 'ma_code')->ignore($discountCode?->id),
            ],
            'loai_giam' => ['required', Rule::in(['phan_tram', 'so_tien_co_dinh'])],
            'gia_tri' => ['required', 'integer', 'min:1', 'max:999999999999', Rule::when($request->input('loai_giam') === 'phan_tram', ['max:100'])],
            'gia_tri_toi_da' => ['nullable', 'integer', 'min:1', 'max:999999999999', Rule::excludeIf($request->input('loai_giam') !== 'phan_tram')],
            'don_toi_thieu' => ['required', 'integer', 'min:0', 'max:99999999999999'],
            'gioi_han_luot' => ['nullable', 'integer', 'min:1', 'max:4294967295'],
            'ngay_bat_dau' => ['nullable', 'date'],
            'ngay_het_han' => ['nullable', 'date', 'after_or_equal:ngay_bat_dau'],
            'dang_hoat_dong' => ['required', 'boolean'],
        ]);
    }
}
