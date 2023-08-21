<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\NewPasswordController;
use App\Http\Controllers\Admin\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\GeneralSettingController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\PricingPlanController;
use App\Http\Controllers\Admin\SupportTicketController;
use App\Http\Controllers\Admin\MailConfigurationController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\SmsGatewayController;
use App\Http\Controllers\Admin\PhoneBookController;
use App\Http\Controllers\Admin\SmsController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\AndroidApiController;
use App\Http\Controllers\Admin\ManageEmailController;
use App\Http\Controllers\Admin\OwnGroupController;
use App\Http\Controllers\Admin\OwnContactController;
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\ManualPaymentGatewayController;
use App\Http\Controllers\Admin\WhatsappController;
use App\Http\Controllers\Admin\WhatsappDeviceController;
use App\Http\Controllers\Admin\GlobalWorldController;




Route::prefix('admin')->name('admin.')->group(function () {
	Route::get('/', [LoginController::class, 'showLogin'])->name('login');
	Route::post('authenticate', [LoginController::class, 'authenticate'])->name('authenticate');
	Route::get('logout', [LoginController::class, 'logout'])->name('logout');

	Route::get('forgot-password', [NewPasswordController::class, 'create'])->name('password.request');
	Route::post('password/email', [NewPasswordController::class, 'store'])->name('password.email');
	Route::get('password/verify/code', [NewPasswordController::class, 'passwordResetCodeVerify'])->name('password.verify.code');
	Route::post('password/code/verify', [NewPasswordController::class, 'emailVerificationCode'])->name('email.password.verify.code');

	Route::get('reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset/password', [ResetPasswordController::class, 'store'])->name('password.reset.update');


    Route::get('/', [LoginController::class, 'showLogin'])->name('login');
	Route::post('authenticate', [LoginController::class, 'authenticate'])->name('authenticate');
	Route::get('logout', [LoginController::class, 'logout'])->name('logout');

	Route::get('forgot-password', [NewPasswordController::class, 'create'])->name('password.request');
	Route::post('password/email', [NewPasswordController::class, 'store'])->name('password.email');
	Route::get('password/verify/code', [NewPasswordController::class, 'passwordResetCodeVerify'])->name('password.verify.code');
	Route::post('password/code/verify', [NewPasswordController::class, 'emailVerificationCode'])->name('email.password.verify.code');

	Route::get('reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset/password', [ResetPasswordController::class, 'store'])->name('password.reset.update');
	// demo.mode
	Route::middleware(['admin','demo.mode'])->group(function () {
		//Dashboard
		Route::get('dashboard', [AdminController::class, 'index'])->name('dashboard');
		
        Route::get('/select/search', [AdminController::class, 'selectSearch'])->name('email.select2');
		Route::get('profile', [AdminController::class, 'profile'])->name('profile');
		Route::post('profile/update', [AdminController::class, 'profileUpdate'])->name('profile.update');
		Route::get('password', [AdminController::class, 'password'])->name('password');
		Route::post('password/update', [AdminController::class, 'passwordUpdate'])->name('password.update');
		Route::get('generate/api-key', [AdminController::class, 'generateApiKey'])->name('generate.api.key');
		Route::post('save/generate/api-key', [AdminController::class, 'saveGenerateApiKey'])->name('save.generate.api.key');

		//Manage Customer
		Route::get('users', [CustomerController::class, 'index'])->name('user.index');
		Route::get('user/active', [CustomerController::class, 'active'])->name('user.active');
		Route::get('user/banned', [CustomerController::class, 'banned'])->name('user.banned');
		Route::get('user/detail/{id}', [CustomerController::class, 'details'])->name('user.details');
		Route::post('user/update/{id}', [CustomerController::class, 'update'])->name('user.update');
		Route::get('user/search/{scope}', [CustomerController::class, 'search'])->name('user.search');

		Route::get('user/sms/contact/log/{id}', [CustomerController::class, 'contact'])->name('user.sms.contact');
		Route::get('user/sms/log/{id}', [CustomerController::class, 'sms'])->name('user.sms');

		Route::get('user/email/contact/log/{id}', [CustomerController::class, 'emailContact'])->name('user.email.contact');
		Route::get('user/email/log/{id}', [CustomerController::class, 'emailLog'])->name('user.email');
		Route::post('user/store/', [CustomerController::class, 'store'])->name('user.store');
		Route::post('user/add/return/', [CustomerController::class, 'addReturnCredit'])->name('user.add.return');

		//General Setting
		Route::get('general/setting', [GeneralSettingController::class, 'index'])->name('general.setting.index');
		Route::post('general/setting/store', [GeneralSettingController::class, 'store'])->name('general.setting.store');
		Route::get('general/setting/cache/clear', [GeneralSettingController::class, 'cacheClear'])->name('general.setting.cache.clear');
		Route::get('general/setting/passport/key', [GeneralSettingController::class, 'installPassportKey'])->name('general.setting.passport.key');

		Route::get('system/info', [GeneralSettingController::class, 'systemInfo'])->name('system.info');
		Route::get('social/login', [GeneralSettingController::class, 'socialLogin'])->name('general.setting.social.login');
		Route::post('social/login/update', [GeneralSettingController::class, 'socialLoginUpdate'])->name('social.login.update');
		Route::get('frontend/section', [GeneralSettingController::class, 'frontendSection'])->name('general.setting.frontend.section');
		Route::post('frontend/section/store', [GeneralSettingController::class, 'frontendSectionStore'])->name('general.setting.frontend.section.store');
		//Currency
		Route::get('general/setting/currencies', [CurrencyController::class, 'index'])->name('general.setting.currency.index');
		Route::post('general/setting/currency/store', [CurrencyController::class, 'store'])->name('general.setting.currency.store');
		Route::post('general/setting/currency/update', [CurrencyController::class, 'update'])->name('general.setting.currency.update');
		Route::post('general/setting/currency/delete', [CurrencyController::class, 'delete'])->name('general.setting.currency.delete');

		//Pricing Plan
		Route::get('plans', [PricingPlanController::class, 'index'])->name('plan.index');
		Route::get('plans/status', [PricingPlanController::class, 'status'])->name('plan.status');
		Route::post('plans/store', [PricingPlanController::class, 'store'])->name('plan.store');
		Route::post('plans/update', [PricingPlanController::class, 'update'])->name('plan.update');
		Route::post('plans/delete', [PricingPlanController::class, 'delete'])->name('plan.delete');
		Route::get('plans/subscription', [PricingPlanController::class, 'subscription'])->name('plan.subscription');
		Route::get('plans/subscription/search', [PricingPlanController::class, 'search'])->name('plan.subscription.search');
		Route::post('plans/subscription/approved', [PricingPlanController::class, 'subscriptionApproved'])->name('plan.subscription.approved');

		//Support Ticket
		Route::get('support/tickets', [SupportTicketController::class, 'index'])->name('support.ticket.index');
		Route::post('support/ticket/reply/{id}', [SupportTicketController::class, 'ticketReply'])->name('support.ticket.reply');
		Route::post('support/ticket/closed/{id}', [SupportTicketController::class, 'closedTicket'])->name('support.ticket.closeds');
		Route::get('support/running/tickets', [SupportTicketController::class, 'running'])->name('support.ticket.running');
		Route::get('support/tickets/replied', [SupportTicketController::class, 'replied'])->name('support.ticket.replied');
		Route::get('support/ticket/answered', [SupportTicketController::class, 'answered'])->name('support.ticket.answered');
		Route::get('support/ticket/closeds', [SupportTicketController::class, 'closed'])->name('support.ticket.closed');
		Route::get('support/ticket/details/{id}', [SupportTicketController::class, 'ticketDetails'])->name('support.ticket.details');
		Route::get('support/ticket/download/{id}', [SupportTicketController::class, 'supportTicketDownlode'])->name('support.ticket.download');
		Route::get('support/ticket/search/{scope}', [SupportTicketController::class, 'search'])->name('support.ticket.search');

		//Mail Configuration
		Route::get('mail/configuration', [MailConfigurationController::class, 'index'])->name('mail.configuration');
		Route::post('mail/add', [MailConfigurationController::class, 'mailAdd'])->name('mail.add');
		Route::post('mail/update/{id}', [MailConfigurationController::class, 'mailUpdate'])->name('mail.update');
		Route::get('mail/edit/{id}', [MailConfigurationController::class, 'edit'])->name('mail.edit');
		Route::post('mail/send/method', [MailConfigurationController::class, 'sendMailMethod'])->name('mail.send.method');
		Route::get('global/template', [MailConfigurationController::class, 'globalTemplate'])->name('mail.global.template');
		Route::post('global/template/update', [MailConfigurationController::class, 'globalTemplateUpdate'])->name('global.template.update');
		// test mail route
		Route::post('mail/test/{id}', [MailConfigurationController::class, 'mailTester'])->name('mail.test');

		//Email Templates
		Route::get('mail/templates', [EmailTemplateController::class, 'index'])->name('mail.templates.index');
		Route::get('mail/template/edit/{id}', [EmailTemplateController::class, 'edit'])->name('mail.templates.edit');
		Route::post('mail/template/update/{id}', [EmailTemplateController::class, 'update'])->name('mail.templates.update');

		//Payment Method
		Route::get('payment/methods', [PaymentMethodController::class, 'index'])->name('payment.method');
		Route::post('payment/update/{id}', [PaymentMethodController::class, 'update'])->name('payment.update');
		Route::get('payment/method/edit/{slug}/{id}', [PaymentMethodController::class, 'edit'])->name('payment.edit');


		//Manual Payment Method
		Route::get('manual/payment/methods', [ManualPaymentGatewayController::class, 'index'])->name('manual.payment.index');
		Route::get('manual/payment/create', [ManualPaymentGatewayController::class, 'create'])->name('manual.payment.create');
		Route::post('manual/payment/store', [ManualPaymentGatewayController::class, 'store'])->name('manual.payment.store');
		Route::get('manual/payment/edit/{id}', [ManualPaymentGatewayController::class, 'edit'])->name('manual.payment.edit');
		Route::post('manual/payment/update/{id}', [ManualPaymentGatewayController::class, 'update'])->name('manual.payment.update');
		Route::post('manual/payment/delete', [ManualPaymentGatewayController::class, 'delete'])->name('manual.payment.delete');



		//Report and logs
		Route::get('report/transactions', [ReportController::class, 'transaction'])->name('report.transaction.index');
		Route::get('report/transactions/search', [ReportController::class, 'transactionSearch'])->name('report.transaction.search');

		Route::get('report/sms/credits', [ReportController::class, 'credit'])->name('report.credit.index');
		Route::get('report/sms/credit/search', [ReportController::class, 'creditSearch'])->name('report.credit.search');

		Route::get('report/whatsapp/credits', [ReportController::class, 'whatsappcredit'])->name('report.whatsapp.index');
		Route::get('report/whatsapp/credit/search', [ReportController::class, 'whatsappcreditSearch'])->name('report.whatsapp.search');

		Route::get('report/email/credits', [ReportController::class, 'emailCredit'])->name('report.email.credit.index');
		Route::get('report/email/credit/search', [ReportController::class, 'emailCreditSearch'])->name('report.email.credit.search');

		Route::get('report/payment/log', [ReportController::class, 'paymentLog'])->name('report.payment.index');
		Route::get('report/payment/detail/{id}', [ReportController::class, 'paymentDetail'])->name('report.payment.detail');
		Route::post('report/payment/approve', [ReportController::class, 'approve'])->name('report.payment.approve');
		Route::post('report/payment/reject', [ReportController::class, 'reject'])->name('report.payment.reject');

		Route::get('report/payment/search', [ReportController::class, 'paymentLogSearch'])->name('report.payment.search');
		Route::get('report/subscriptions', [ReportController::class, 'subscription'])->name('report.subscription.index');
		Route::get('report/subscription/search', [ReportController::class, 'subscriptionSearch'])->name('report.subscription.search');

		//SMS Gateway
		Route::get('sms/gateway', [SmsGatewayController::class, 'index'])->name('gateway.sms.index');
		Route::get('sms/gateway/edit/{id}', [SmsGatewayController::class, 'edit'])->name('gateway.sms.edit');
		Route::post('sms/gateway/update/{id}', [SmsGatewayController::class, 'update'])->name('sms.gateway.update');
		Route::post('sms/default/gateway', [SmsGatewayController::class, 'defaultGateway'])->name('sms.default.gateway');

		//whatsapp Gateway
        Route::prefix('whatsapp/gateway/')->name('gateway.whatsapp.')->group(function () {
            Route::get('create', [WhatsappDeviceController::class, 'create'])->name('create');
            Route::post('create', [WhatsappDeviceController::class, 'store']);
            Route::get('edit/{id}', [WhatsappDeviceController::class, 'edit'])->name('edit');
            Route::post('update', [WhatsappDeviceController::class, 'update'])->name('update');
            Route::post('status-update', [WhatsappDeviceController::class, 'statusUpdate'])->name('status-update');
            Route::post('delete', [WhatsappDeviceController::class, 'delete'])->name('delete');
            Route::post('qr-code', [WhatsappDeviceController::class, 'getWaqr'])->name('qrcode');
        });

		//group
		Route::get('sms/groups', [PhoneBookController::class, 'smsGroupIndex'])->name('group.sms.index');
		Route::get('sms/group/contact/{id}', [PhoneBookController::class, 'smsContactByGroup'])->name('group.sms.groupby');
		Route::get('email/groups', [PhoneBookController::class, 'emailGroupIndex'])->name('group.email.index');
		Route::get('email/contact/{id}', [PhoneBookController::class, 'emailContactByGroup'])->name('group.email.groupby');


        //Group SMS
        Route::prefix('sms/own/')->name('group.own.sms.')->group(function () {
            Route::get('groups', [OwnGroupController::class, 'smsIndex'])->name('index');
            Route::post('group/store', [OwnGroupController::class, 'smsStore'])->name('store');
            Route::post('group/update', [OwnGroupController::class, 'smsUpdate'])->name('update');
            Route::post('group/delete', [OwnGroupController::class, 'smsDelete'])->name('delete');
            Route::get('group/contact/{id}', [OwnGroupController::class, 'smsOwnContactByGroup'])->name('contact');
        });

        //Group Email
        Route::prefix('email/own/')->name('group.own.email.')->group(function () {
            Route::get('groups', [OwnGroupController::class, 'emailIndex'])->name('index');
            Route::post('group/store', [OwnGroupController::class, 'emailStore'])->name('store');
            Route::post('group/update', [OwnGroupController::class, 'emailUpdate'])->name('update');
            Route::post('group/delete', [OwnGroupController::class, 'emailDelete'])->name('delete');
            Route::get('group/contact/{id}', [OwnGroupController::class, 'emailOwnContactByGroup'])->name('contact');
        });

		//Email
        Route::prefix('email/own/')->name('contact.email.own.')->group(function () {
            Route::get('contacts', [OwnContactController::class, 'emailContactIndex'])->name('index');
            Route::post('contact/store', [OwnContactController::class, 'emailContactStore'])->name('store');
            Route::post('contact/update', [OwnContactController::class, 'emailContactUpdate'])->name('update');
            Route::post('contact/delete', [OwnContactController::class, 'emailContactDelete'])->name('delete');
            Route::post('contact/import', [OwnContactController::class, 'emailContactImport'])->name('import');
            Route::get('contact/export', [OwnContactController::class, 'emailContactExport'])->name('export');
            Route::get('contact/group/export/{id}', [OwnContactController::class, 'emailContactGroupExport'])->name('group.export');
        });

        //Sms
        Route::prefix('sms/own/')->name('contact.sms.own.')->group(function () {
            Route::get('contacts', [OwnContactController::class, 'smsContactIndex'])->name('index');
            Route::post('contact/store', [OwnContactController::class, 'smsContactStore'])->name('store');
            Route::post('contact/update', [OwnContactController::class, 'smsContactUpdate'])->name('update');
            Route::post('contact/delete', [OwnContactController::class, 'smsContactDelete'])->name('delete');
            Route::post('contact/import', [OwnContactController::class, 'smsContactImport'])->name('import');
            Route::get('contact/export', [OwnContactController::class, 'smsContactExport'])->name('export');
            Route::get('contact/group/export/{id}', [OwnContactController::class, 'smsContactGroupExport'])->name('group.export');
        });

		Route::get('sms/contacts', [PhoneBookController::class, 'smsContactIndex'])->name('contact.sms.index');
		Route::get('email/contacts', [PhoneBookController::class, 'emailContactIndex'])->name('contact.email.index');

		Route::get('sms/contact/export', [PhoneBookController::class, 'contactExport'])->name('contact.sms.export');
		Route::get('email/contact/export', [PhoneBookController::class, 'emailContactExport'])->name('contact.email.export');

		//sms log
        Route::prefix('sms/')->name('sms.')->group(function () {
            Route::get('', [SmsController::class, 'index'])->name('index');
            Route::get('create', [SmsController::class, 'create'])->name('create');
            Route::post('store', [SmsController::class, 'store'])->name('store');
            Route::get('pending', [SmsController::class, 'pending'])->name('pending');
            Route::get('delivered', [SmsController::class, 'success'])->name('success');
            Route::get('schedule', [SmsController::class, 'schedule'])->name('schedule');
            Route::get('failed', [SmsController::class, 'failed'])->name('failed');
            Route::get('processing', [SmsController::class, 'processing'])->name('processing');
            Route::get('search/{scope}', [SmsController::class, 'search'])->name('search');
            Route::post('status/update', [SmsController::class, 'smsStatusUpdate'])->name('status.update');
            Route::post('delete', [SmsController::class, 'delete'])->name('delete');
        });

		//Whatsapp log
        Route::prefix('whatsapp/')->name('whatsapp.')->group(function () {
            Route::get('', [WhatsappController::class, 'index'])->name('index');
            Route::get('create', [WhatsappController::class, 'create'])->name('create');
            Route::post('store', [WhatsappController::class, 'store'])->name('store');
            Route::get('pending', [WhatsappController::class, 'pending'])->name('pending');
            Route::get('delivered', [WhatsappController::class, 'success'])->name('success');
            Route::get('schedule', [WhatsappController::class, 'schedule'])->name('schedule');
            Route::get('failed', [WhatsappController::class, 'failed'])->name('failed');
            Route::get('processing', [WhatsappController::class, 'processing'])->name('processing');
            Route::get('search/{scope}', [WhatsappController::class, 'search'])->name('search');
            Route::post('status/update', [WhatsappController::class, 'statusUpdate'])->name('status.update');
            Route::post('delete', [WhatsappController::class, 'delete'])->name('delete');
        });

		//Email log
        Route::prefix('email/')->name('email.')->group(function () {
            Route::get('', [ManageEmailController::class, 'index'])->name('index');
            Route::get('send', [ManageEmailController::class, 'create'])->name('send');
            Route::post('store', [ManageEmailController::class, 'store'])->name('store');
            Route::get('pending', [ManageEmailController::class, 'pending'])->name('pending');
            Route::get('delivered', [ManageEmailController::class, 'success'])->name('success');
            Route::get('schedule', [ManageEmailController::class, 'schedule'])->name('schedule');
            Route::get('failed', [ManageEmailController::class, 'failed'])->name('failed');
            Route::get('search/{scope}', [ManageEmailController::class, 'search'])->name('search');
            Route::post('status/update', [ManageEmailController::class, 'emailStatusUpdate'])->name('status.update');
            Route::get('single/mail/send/{id}', [ManageEmailController::class, 'emailSend'])->name('single.mail.send');
            Route::get('view/{id}', [ManageEmailController::class, 'viewEmailBody'])->name('view');
            Route::post('delete', [ManageEmailController::class, 'delete'])->name('delete');
        });

        //android gateway
        Route::prefix('android/gateway/')->name('gateway.sms.android.')->group(function () {
            Route::get('gateway', [AndroidApiController::class, 'index'])->name('index');
            Route::post('store', [AndroidApiController::class, 'store'])->name('store');
            Route::post('update', [AndroidApiController::class, 'update'])->name('update');
            Route::get('sim/list/{id}', [AndroidApiController::class, 'simList'])->name('sim.index');
            Route::post('delete/', [AndroidApiController::class, 'delete'])->name('delete');
            Route::post('sim/delete/', [AndroidApiController::class, 'simNumberDelete'])->name('sim.delete');
        });

		//Template
		Route::get('sms/templates', [TemplateController::class, 'index'])->name('template.index');
		Route::post('sms/template/store', [TemplateController::class, 'store'])->name('template.store');
		Route::post('sms/template/update', [TemplateController::class, 'update'])->name('template.update');
		Route::post('sms/template/delete', [TemplateController::class, 'delete'])->name('template.delete');
		// user template
		Route::get('sms/user/templates', [TemplateController::class, 'userIndex'])->name('template.user.index');
		Route::post('sms/template/user/status', [TemplateController::class, 'updateStatus'])->name('template.userStatus.update');

		//Language
        Route::prefix('languages/')->name('language.')->group(function () {
            Route::get('', [LanguageController::class, 'index'])->name('index');
            Route::post('store', [LanguageController::class, 'store'])->name('store');
            Route::post('update', [LanguageController::class, 'update'])->name('update');
            Route::get('translate/{code}', [LanguageController::class, 'translate'])->name('translate');
            Route::post('data/store', [LanguageController::class, 'languageDataStore'])->name('data.store');
            Route::post('data/update', [LanguageController::class, 'languageDataUpdate'])->name('data.update');
            Route::post('delete', [LanguageController::class, 'languageDelete'])->name('delete');
            Route::post('data/delete', [LanguageController::class, 'languageDataUpDelete'])->name('data.delete');
            Route::post('default', [LanguageController::class, 'setDefaultLang'])->name('default');
        });

        // Global world
		Route::get('spam/word', [GlobalWorldController::class, 'index'])->name('spam.word.index');
		Route::post('spam/word/store', [GlobalWorldController::class, 'store'])->name('spam.word.store');
		Route::post('spam/word/update', [GlobalWorldController::class, 'update'])->name('spam.word.update');
		Route::post('spam/word/delete', [GlobalWorldController::class, 'delete'])->name('spam.word.delete');
	});
});
