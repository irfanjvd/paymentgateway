<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

use App\Models\User;
use App\Models\Packages;
use App\Models\UserPackages;
use App\Http\Requests\SignupRequest;
use App\Mail\SendHtmlEmail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Subscription;



class ApiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    function login(Request $request){
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            if($user->email_verified_at!=""){
                foreach ($user->tokens as $token) {
                    $token->revoke();
                }
                $success['token'] = $user->createToken('LaravelVueFirst_Token')->accessToken;
                $success['user'] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                ];
                return response()->json([
                    'status' => true,
                    'ResponseCode' => 200,
                    'data' => $success,
                    'message' => "Login Successfully",
                ]);        
            }else{
                return response()->json([
                    'status' => false,
                    'ResponseCode' => 200,
                    'data' => [],
                    'message' => "Please verify your email address first",
                ]);        
            }
        } else {
            return response()->json([
                'status' => false,
                'ResponseCode' => 201,
                'message' => "Invalid Username Password"
            ],200);
        }

    }

    public function signup(Request $request){
        if($request->resend==1){
            $user=User::where(['email' => $request->email])->first();
            $data=[
                'status'  =>  true,
                'message'   => 'Email sent successfully!!!',
            ];
            //$url=\App::make('url')->route('verify_email',['id' => $user->id]);
            $url=env('APP_LINK').$user->id;
            $link="<a href='$url'>".$url."</a>";
            $email_data=[
                'to' => $request->email,
                'subject' => 'Verify Email',
                'message' => "Verify Your Email <br> $link<br> Your code will expire in 10 minutes.<br><br> If you didn`t request this code, it was likely sent by mistake and you may ignore this."
            ];
            //$this->sendEmailGeneric($email_data);
            $this->sendEmail($email_data);
            return response()->json($data, 200);
        }else{
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users|max:255',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'errors' => $validator->errors()], 200);
            }else{
                $data=$request->all();
                $data['password']=Hash::make($data['password']);
                $user=User::create($data);
                $data=[
                    'status'  =>  true,
                    'message'   => 'User Signup Successfully!!!',
                ];
                //$url=\App::make('url')->route('verify_email',['id' => $user->id]);
                $url=env('APP_LINK').$user->id;
                $link="<a href='$url'>".$url."</a>";
                $email_data=[
                    'to' => $request->email,
                    'subject' => 'Verify Email',
                    'message' => "Verify Your Email <br> $link<br> Your code will expire in 10 minutes.<br><br> If you didn`t request this code, it was likely sent by mistake and you may ignore this."
                ];
                //$this->sendEmailGeneric($email_data);
                $this->sendEmail($email_data);
                return response()->json($data, 200);
            }    
        }
        
        

    }

    public function changePassword(Request $request){
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }else{
            $data=$request->all();
            $data_save['password']=Hash::make($data['password']);
            User::find(Auth::user()->id)->update($data_save);
            $data=[
                'status'  =>  true,
                'message'   => 'Password Changed Successfully!!!',
            ];
            //$url=\App::make('url')->route('verify_email',['id' => $user->id]);
 	        $url=env('APP_VERIFY_LINK');
            $link="<a href='$url'>".$url."</a>";
            $email_data=[
                'to' => $request->email,
                'subject' => 'Verify Email',
                'message' => "Verify Your Email <br> $link<br> Your code will expire in 10 minutes.<br><br> If you didn`t request this code, it was likely sent by mistake and you may ignore this."
            ];
            return response()->json($data, 200);
        }
    }

    public function resetPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }else{
            $data=$request->all();
            $data_save['password']=Hash::make($data['password']);
            User::where(['email' => $request->email])->update($data_save);
            $data=[
                'status'  =>  true,
                'message'   => 'Password Changed Successfully!!!',
            ];
            return response()->json($data, 200);
        }
    }

    function forgotPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required',
        ]);
        $user=User::where(['email' => $request->email])->first();

        if($user){
            // $url=\App::make('url')->route('change_password');
            $url=env('APP_CHANGE_PASS_LINK').$user->id;
            $link="<a href='$url'>".$url."</a>";
            $email_data=[
                'to' => $request->email,
                'subject' => 'Change Password',
                'message' => "Click on below to link to change your password <br> $link<br> "
            ];
            
            $this->sendEmail($email_data);
            $data=[
                'status' => true,
                'message' => 'Change password request sent!!!',
                'data' => ['user_id' => $user->id]
            ]; 
        }else{
            $data=[
                'status' => false,
                'message' => 'User doesn`t exists!!!'
            ];    
        }
        return response()->json($data,200);
    }

    function verifyEmail(Request $request){
        $user=User::find($request->id);
        if($user){
            $user->update(['email_verified_at' => now()]);
            $data=[
                'status' => true,
                'message' => 'Email Verified Successfully!!!'
            ];
        }else{
            $data=[
                'status' => true,
                'message' => 'Invalid user!!!'
            ];
        }
        
        return response()->json($data, 200);
    }

    public function getSubscriptions(Request $request){
        $user_id = Auth::id();
        $packages=Packages::where(['status'=>1])->get();
        $package_data=[];
        if(!empty($packages)){
            foreach($packages as $key=>$val){
                $package_data[]=[
                    'type' => $val->type,
                    'description' => $val->description,
                    'link' => $val->link."?client_reference_id=".base64url_encode($user_id."_".$val->id)
                ];
            }
        }
        return response()->json($package_data, 200);
    }

    public function getUserSubscription(Request $request){
        
        $user_packages=UserPackages::where(['user_id'=>$user_id,'status'=>1])->get();
        return response()->json($user_packages, 200);
    }

    public function getCheckoutSession(Request $request){
        // Set your secret key. Remember to switch to your live secret key in production!
        Stripe::setApiKey(config('services.stripe.secret'));

        // Retrieve the session by ID
        $sessionId = $request->checkout_session_id; // Replace with your actual session ID
        try {
            $checkoutSession = Session::retrieve($sessionId);
            // You can access details like:
            $customerEmail = $checkoutSession->customer_email;
            $amountTotal = $checkoutSession->amount_total;
            $paymentStatus = $checkoutSession->payment_status;
            // Add more fields as needed
        } catch (\Exception $e) {
            // Handle the error appropriately
            return response()->json(['error' => $e->getMessage()], 400);
        }
        $user_sub_arr=explode("_",base64url_decode($checkoutSession->client_reference_id));
        $package_id=$user_sub_arr[1];
        $customer_id=$checkoutSession->customer;
        $subscription_id=$checkoutSession->subscription;

        $user_id = Auth::id();
        // User::find($user_id)->update(['stripe_customer_id'=>$customer_id]);
        $record=UserPackages::create(['user_id'=>$user_id,'package_id'=>$package_id,'subscription_id' => $subscription_id,'stripe_customer_id'=>$stripe_customer_id]);
        // return $this->getUserSubscription($request);
        return response()->json($checkoutSession);
    }

    public function checkUserHaveSubscription(Request $request)
    {
        $user = auth()->user(); // Assuming you're using Laravel's authentication system
        $stripeCustomerId = $user->stripe_customer_id;

        if (!$stripeCustomerId) {
            return response()->json(['status'=>false,'has_subscription' => false]);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $subscriptions = Subscription::all([
                'customer' => $stripeCustomerId,
                'status' => 'active',
                'limit' => 1,
            ]);
            if ($subscriptions->data) {
                $sub_data=[
                    'has_subscription' => true,
                    'type' => $subscriptions->data[0]->items->data[0]->plan->interval,
                    'start_date' => date('Y-m-d',$subscriptions->data[0]->current_period_start),
                    'end_date' => date('Y-m-d',$subscriptions->data[0]->current_period_end)
                ];
                // return response()->json([
                //     'has_subscription' => true,
                //     'subscription' => $subscriptions->data[0]
                // ]);
                return response()->json($sub_data);
            } else {
                return response()->json(['has_subscription' => false]);
            }
        } catch (\Exception $e) {
            return response()->json(['status'=>false,'error' => $e->getMessage()], 400);
        }
    }

    public function cancelSubscription(Request $request){
        Stripe::setApiKey(env('STRIPE_SECRET'));
        $subscription = \Stripe\Subscription::update("sub_1PoTw52NZ1xVuGdUeVfkv3a6", [
            'cancel_at_period_end' => true,
        ]);
        
    }
    
    function sendEmail($data){
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME'); // SMTP username
            $mail->Password = env('MAIL_PASSWORD'); // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            //Recipients
            $mail->setFrom('info@chatwithmodules.com', 'Chat With Moduels');
            $mail->addAddress($data['to']);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $data['subject'];
            $mail->Body    = $data['message'];

            $mail->send();
            // echo 'Email has been sent';
        } catch (Exception $e) {
            // echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
            // error_log("Mailer Error: {$mail->ErrorInfo}");
        }
        
        
    }
    function sendEmailGeneric($data){
        try {
            Mail::to($data['to'])->send(new SendHtmlEmail($data));
            // Redirect back to the same page or wherever needed
            return redirect()->back()->with('success', 'Email Successfully sent!');
        } catch (\Exception $e) {
            dd($e);
            // Flash error message to session
            $request->session()->flash('error', 'Failed to queue email for sending. Please try again.');
            // Redirect back to the same page or wherever needed
            return redirect()->back();
        }
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }

    
    public function testmail(){
        $mail = new PHPMailer(true);

try {
    //Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
    $mail->SMTPAuth = true;
    $mail->Username = ''; // SMTP username
    $mail->Password = ''; // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    //Recipients
    $mail->setFrom('info@cargoflyers.com', 'Cargo Flyers');
    $mail->addAddress("irfanjvd@gmail.com");

    // Content
    $mail->isHTML(true);
    $mail->Subject = "test subject";
    $mail->Body    = "test message";

    $mail->send();
    echo 'Email has been sent';
} catch (Exception $e) {
    echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    error_log("Mailer Error: {$mail->ErrorInfo}");
}
        }

}
