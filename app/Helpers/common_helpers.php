<?php
@include 'dropdown_helpers.php';
use App\Models\AttachFile;

function is_allowed($check = false, $type = false)
{
	if (!Auth::id())
  	{
		return false;
	}

	if ($type == 'role') {
		if (Auth::user()->hasRole('admin') || Auth::user()->hasRole($check)) {
	    	return true;
	  	}
	}

	if ($type == 'permission') {
		if (Auth::user()->can('all') || Auth::user()->can($check)) {
	    	return true;
	  	}
	}

	return false;
}

if (!function_exists('base64url_encode')) {
    function base64url_encode($data){
        return generate_string(30) . rtrim(strtr(base64_encode($data), '+/', '-_'), '=') . generate_string(30);
    }
}

if (!function_exists('base64url_decode')) {
    function base64url_decode($data){
        $str_start = substr($data, 0, 30);
        $str_end = substr($data, -30);
        $find = array($str_start, $str_end);
        $rep = array('', '');
        $data = str_replace($find, $rep, $data);
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}

function generate_string($strength = 30,$integer=''){
    if($integer==1){
        $input = '0123456789';
    }else{
        $input = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    }

    $input_length = strlen($input);
    $random_string = '';
    for ($i = 0; $i < $strength; $i++) {
        $random_character = $input[mt_rand(0, $input_length - 1)];
        $random_string .= $random_character;
    }

    return $random_string;
}

if (!function_exists('hasPermission')) {
    function hasPermission($permission){
        $role_id = Session::get('user_info.role_id');
        if ($role_id == 1) {
            return true;
        }
        $role = Spatie\Permission\Models\Role::find($role_id);
        if($role!=null){
            try {
                return $role->hasPermissionTo($permission);
            } catch (Exception $e) {
                return false;
            }
        }else{
            return false;
        }

    }
}

if (!function_exists('sendMail')) {
	function sendMail($data,$request){
		Mail::send(['html'=>'mail'], $data, function($message)use ($request) {
            $message->to($request->email, 'Activate Account')->subject('Activate Your Account');
            $message->from('delickate@yarnonline.net','LE PARKING');
		});
	}
}

if (!function_exists('sendPasswordMail')) {
    function sendPasswordMail($data,$request){
        Mail::send(['html'=>'mail'], $data, function($message)use ($request) {
            $message->to($request->email, 'Password Reset')->subject('Password Reset');
            $message->from('delickate@yarnonline.net','LE PARKING');
        });
    }
}

function attachFiles($files, $record_id, $class_name, $destination){
    if (!is_dir($destination)) @mkdir($destination);
    
    $attachmentFiles = false;
    if ($files) {
        if (!empty($files)) {
            foreach ($files as $file) {
                $originalName = $file->getClientOriginalName();
                $new_name = md5(rand() . strtotime(date('Y-m-d H:i:s') . microtime())) . $originalName;
//                    $destination .= "/". $new_name;
                $fileDestination = $destination . DIRECTORY_SEPARATOR . $new_name;
                $result=Storage::disk('public')->put($fileDestination, File::get($file));
                $file_path = \Storage::url($destination.$new_name);
                $path = asset($file_path);
                
                $attachmentFiles[] = [
                    // 'attachment_type'=>$attachment_type,
                    'url' => $path,
                    'file_name' => $new_name,
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize(),
                    'original_name' => $originalName,
                    'created_by' => Auth::user()->id,
                    'created_at' => now(),
                    'attachable_id' => $record_id,
                    'attachable_type' => $class_name
                ];
            }
        }
    }
    if ($attachmentFiles) {
        AttachFile::insert($attachmentFiles);
    }
    return $attachmentFiles;
}


function uploadFile($file, $destination){
    if (!is_dir($destination)) @mkdir($destination);
    
    $attachmentFiles = false;
    if ($file) {
        if (!empty($file)) {
            $originalName = $file->getClientOriginalName();
            $new_name = md5(rand() . strtotime(date('Y-m-d H:i:s') . microtime())) . $originalName;
            $fileDestination = $destination . DIRECTORY_SEPARATOR . $new_name;
            $result=Storage::disk('public')->put($fileDestination, File::get($file));
            $file_path = \Storage::url($destination.$new_name);
            $path = asset($file_path);
        }
    }
    return $path;
}

function renderFile($ext, $path, $width = '50px', $iconOnly = false, $faSize = 'fa-3x')
{
    $path=asset("storage/$path");
    $return = "";
    if (strtolower($ext) == "xls" || strtolower($ext) == "xlsx") {
        $return = "<a href='$path'>
            <i class='fa fa-file-excel-o $faSize pull-left'></i>
        </a>";
    } elseif (strtolower($ext) == "doc" || strtolower($ext) == "docx") {
        $return = "<a href='$path'>
            <i class='fa fa-file-word-o $faSize pull-left'></i>
        </a>";

    } elseif (strtolower($ext) == "pdf") {
        $return = "<a href='$path'>
            <i class='fa fa-file-pdf-o $faSize  pull-left'></i>
        </a>";
    } elseif (strtolower($ext) == "txt") {
        $return = "<a href='$path'>
            <i class='fa fa-file-text-o $faSize pull-left'></i>
        </a>";
    } elseif (strtolower($ext) == "jpg" || strtolower($ext) == "jpeg" || strtolower($ext) == "png" || strtolower($ext) == "jfif") {
        if ($iconOnly) {
            $return = "<a href='path' target='_blank' src='$path' class='modalShow'><i class='fa fa-file-image-o " . $faSize . " pull-left'></i></a>";
        } else {
            $return = "<a href='$path' target='_blank'><img src='$path' class='modalShow pull-left' style='width:$width; cursor:pointer;padding:10px;'></a>";
        }
    }
    return $return;
}

