<?php
use Carbon\Carbon;
use App\Models\Admin;
use App\Models\Setting;
use App\Models\Notification;
;

function getPaymentStatus($data, $key)
{
    // Kiểm tra nếu phương thức payment() tồn tại và trả về một query builder
    if (method_exists($data, 'payment')) {
        $payment = $data->payment()->where('form_type', $key)->where('record_id', $data->id)->first();

        // Nếu tìm thấy thông tin thanh toán, trả về nó
        if ($payment) {
            return $payment;
        }
    }

    // Trả về object mặc định
    return (object) [
        'payment_status' => null, // Null khi không tìm thấy
    ];
}

function get($key, $default = null)
{
    $setting = Setting::where('key', $key)->first();
    return $setting ? $setting->value : $default;
}

function getActiveNotifications()
{

    $noti = $notifications = Notification::where('expiry_date', '>', Carbon::now())
        ->where('type', 0)
        ->get();
    return $noti;
}
function getCategories($categories, $old = '', $parentId = 0, $char = '')
{
    $id = request()->route()->category;
    if ($categories) {
        foreach ($categories as $key => $category) {
            if ($category->parent_id == $parentId && $id != $category->id) {

                echo '<option value="' . $category->id . '"';
                if ($old == $category->id) {
                    echo 'selected';
                }
                echo '>' . $char . $category->name . '</option>';
                unset($categories[$key]);
                getCategories($categories, $old, $category->id, $char . '|-');
            }
        }
    }
}


function isAdminActive($email)
{
    $count = Admin::where('email', $email)->where('is_active', '=', '1')->count();
    if ($count) {
        return true;
    }
    return false;
}

if (!function_exists('convert_array')) {
    function convert_array($system = null, $keyword = '', $value = '')
    {
        $temp = [];
        if (is_array($system)) {
            foreach ($system as $key => $val) {
                $temp[$val[$keyword]] = $val[$value];
            }
        }
        if (is_object($system)) {
            foreach ($system as $key => $val) {
                $temp[$val->{$keyword}] = $val->{$value};
            }
        }
        return $temp;
    }
}
if (!function_exists('renderSystemInput')) {
    function renderSystemInput(string $name = '', $type = 'text', $system = null)
    {
        return ' <input type="' . $type . '"
        name="config[' . $name . ']"
        value=" ' . old($name, (isset($system[$name])) ? $system[$name] : "") . '"
        class="form-control"
        placeholder="" >';
    }
}
if (!function_exists('renderSystemTextarea')) {
    function renderSystemTextarea(string $name = '', $system = null)
    {
        return '<textarea name="config[' . $name . ']" value="" class="form-control">'
            . old($name, (isset($system[$name])) ? $system[$name]
                : "") . '</textarea>';
    }
}
if (!function_exists('renderSystemSelect')) {
    function renderSystemSelect($items, string $name = '', $system = null)
    {
        $html = '<select name="config[' . $name . ']" class="form-control">';

        foreach ($items as $key => $item) {
            $html .= '<option ' . (old($name, (isset($system[$name])) ? $system[$name] : "")) ? "select" : '' . ' value=' . $key . '>' . $key . '</option>';
        }

        $html .= '</select>';
        return $html;
    }
}

