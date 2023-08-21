<div id="sideContent" class="side_content">
    <div class="logo_container">
        <div class="logo_name">
            <div class="logo_img">
                <img src="{{showImage(filePath()['site_logo']['path'].'/site_logo.png')}}" alt="{{ translate('Site Logo')}}"> 
            </div>
            <div onclick="showSideBar()" class="cross">
                <i class="lar la-times-circle fs--9 text--light"></i>
            </div>
        </div>
    </div>
    <div class="side_bar_menu_container">
        <div class="side_bar_menu_list">
            <ul>
                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{menuActive('user.dashboard')}}" href="{{route('user.dashboard')}}">
                        <div>
                            <span class="me-3"><i class="fs-5 las la-tachometer-alt text--light me-2"></i></span>{{ translate('Dashboard')}}
                        </div>
                    </a>
                </li>
            </ul>
            <ul>
                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{menuActive('user.plan.create')}}" href="{{route('user.plan.create')}}">
                        <div>
                            <span class="me-3"><i class="fs-5 las la-money-bill-wave text--light me-2"></i></span>{{ translate('Pricing Plan') }}
                        </div>
                    </a>
                </li>

                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{menuActive('user.plan.subscription')}}" href="{{route('user.plan.subscription')}}">
                        <div>
                            <span class="me-3"><i class="fs-5 las la-calendar-day text--light me-2"></i></span>{{ translate('Subscriptions')}}
                        </div>
                    </a>
                </li>
            </ul>

            <h1 class="text-muted ms--1 mb-2 text-capitalize">{{ translate('MESSAGING')}}</h1>
            <ul>
                <li>
                    <a class="ms--1 d--flex align--center {{sidebarMenuActive(['user.sms.send','user.sms.index','user.sms.pending','user.sms.schedule', 'user.sms.delivered', 'user.sms.failed', 'user.sms.search', 'user.sms.date.search', 'user.sms.processing'])}} side_bar_nine_list" href="javascript:void(0)"><div><span class="me-4"><i class="fs-5 las la-envelope text--light"></i></span>{{ translate('SMS')}}</div><i class="las la-angle-down icon9"></i></a>
                    <ul class="first_nine_child {{menuActive('user.sms*',9)}}">
                        <li>
                            <a class="{{menuActive('user.sms.send')}}" href="{{route('user.sms.send')}}"><i class="lab la-jira me-3"></i>{{ translate('Send SMS')}}</a>
                            <a class="{{menuActive('user.sms.index')}}" href="{{route('user.sms.index')}}"><i class="lab la-jira me-3"></i>{{ translate('All SMS')}}</a>
                             <a class="{{menuActive('user.sms.pending')}}" href="{{route('user.sms.pending')}}"><i class="lab la-jira me-3"></i>{{ translate('Pending SMS')}}</a>
                             <a class="{{menuActive('user.sms.schedule')}}" href="{{route('user.sms.schedule')}}"><i class="lab la-jira me-3"></i>{{ translate('Schedule SMS')}}</a>
                              <a class="{{menuActive('user.sms.processing')}}" href="{{route('user.sms.processing')}}"><i class="lab la-jira me-3"></i>{{ translate('Processing SMS')}}</a>
                             <a class="{{menuActive('user.sms.delivered')}}" href="{{route('user.sms.delivered')}}"><i class="lab la-jira me-3"></i>{{ translate('Delivered SMS')}}</a>
                             <a class="{{menuActive('user.sms.failed')}}" href="{{route('user.sms.failed')}}"><i class="lab la-jira me-3"></i>{{ translate('Failed SMS')}}</a>
                        </li>
                    </ul>
                </li>

                <li>
                    <a class="ms--1 d--flex align--center {{sidebarMenuActive(['user.whatsapp.send','user.whatsapp.index','user.whatsapp.pending','user.whatsapp.schedule', 'user.whatsapp.delivered', 'user.whatsapp.failed', 'user.whatsapp.search', 'user.whatsapp.date.search', 'user.whatsapp.processing'])}} side_bar_twenty_three_list" href="javascript:void(0)"><div><span class="me-4"><i class="fs-5 fab fa-whatsapp text--light"></i></span>{{ translate('WhatsApp')}}</div><i class="las la-angle-down icon23"></i></a>
                    <ul class="first_twenty_three_child {{menuActive('user.whatsapp*',23)}}">
                        <li>
                            <a class="{{menuActive('user.whatsapp.send')}}" href="{{route('user.whatsapp.send')}}"><i class="lab la-jira me-3"></i>{{ translate('Send Message')}}</a>
                            <a class="{{menuActive('user.whatsapp.index')}}" href="{{route('user.whatsapp.index')}}"><i class="lab la-jira me-3"></i>{{ translate('All Message')}}</a>
                             <a class="{{menuActive('user.whatsapp.pending')}}" href="{{route('user.whatsapp.pending')}}"><i class="lab la-jira me-3"></i>{{ translate('Pending Message')}}</a>
                             <a class="{{menuActive('user.whatsapp.schedule')}}" href="{{route('user.whatsapp.schedule')}}"><i class="lab la-jira me-3"></i>{{ translate('Schedule Message')}}</a>
                              <a class="{{menuActive('user.whatsapp.processing')}}" href="{{route('user.whatsapp.processing')}}"><i class="lab la-jira me-3"></i>{{ translate('Processing Message')}}</a>
                             <a class="{{menuActive('user.whatsapp.delivered')}}" href="{{route('user.whatsapp.delivered')}}"><i class="lab la-jira me-3"></i>{{ translate('Delivered Message')}}</a>
                             <a class="{{menuActive('user.whatsapp.failed')}}" href="{{route('user.whatsapp.failed')}}"><i class="lab la-jira me-3"></i>{{ translate('Failed Message')}}</a>
                        </li>
                    </ul>
                </li>

                <li>
                    <a class="ms--1 d--flex align--center {{sidebarMenuActive(['user.manage.email.send', 'user.manage.email.index', 'user.manage.email.pending','user.manage.email.schedule','user.manage.email.delivered', 'user.manage.email.failed','user.manage.email.search','user.manage.email.date.search'])}} side_bar_ten_list" href="javascript:void(0)"><div><span class="me-4"><i class="fs-5 las la-envelope-open-text text--light"></i></span>{{ translate('Email')}}</div><i class="las la-angle-down icon10"></i></a>
                    <ul class="first_ten_child {{menuActive('user.manage.email*',10)}}">
                        <li>
                            <a class="{{menuActive('user.manage.email.send')}}" href="{{route('user.manage.email.send')}}"><i class="lab la-jira me-3"></i>{{ translate('Send Email')}}</a>
                            <a class="{{menuActive(['user.manage.email.index','user.manage.email.search', 'user.manage.email.date.search'])}}" href="{{route('user.manage.email.index')}}"><i class="lab la-jira me-3"></i>{{ translate('All Email')}}</a>
                             <a class="{{menuActive('user.manage.email.pending')}}" href="{{route('user.manage.email.pending')}}"><i class="lab la-jira me-3"></i>{{ translate('Pending Email')}}</a>
                             <a class="{{menuActive('user.manage.email.schedule')}}" href="{{route('user.manage.email.schedule')}}"><i class="lab la-jira me-3"></i>{{ translate('Schedule Email')}}</a>
                             <a class="{{menuActive('user.manage.email.delivered')}}" href="{{route('user.manage.email.delivered')}}"><i class="lab la-jira me-3"></i>{{ translate('Delivered Email')}}</a>
                             <a class="{{menuActive('user.manage.email.failed')}}" href="{{route('user.manage.email.failed')}}"><i class="lab la-jira me-3"></i>{{ translate('Failed Email')}}</a>
                        </li>
                    </ul>
                </li> 

                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{menuActive('user.template.index')}}" href="{{route('user.template.index')}}">
                        <div>
                            <span class="me-3"><i class="las la-palette fs-5 text--light me-2"></i></span>{{ translate('SMS Template')}}
                        </div>
                    </a>
                </li>
            </ul>

            <h1 class="text-muted ms--1 mb-2 text-capitalize">{{ translate('CONTACTS')}}</h1>
            <ul>
                <li>
                    <a class="ms--1 d--flex align--center {{sidebarMenuActive(['user.phone.book.group.index', 'user.phone.book.contact.index','user.phone.book.template.index', 'user.phone.book.sms.contact.group'])}} side_bar_twenty_list" href="javascript:void(0)"><div><span class="me-4"><i class="fs-5 las la-sms text--light"></i></span>{{ translate('Phonebook')}}</div><i class="las la-angle-down icon20"></i></a>
                    <ul class="first_twenty_child {{menuActive('user.phone.book*',20)}}">
                        <li>
                            <a class="{{menuActive(['user.phone.book.group.index','user.phone.book.sms.contact.group'])}}" href="{{route('user.phone.book.group.index')}}"><i class="lab la-jira me-3"></i>{{ translate('Groups')}}</a>
                            <a class="{{menuActive('user.phone.book.contact.index')}}" href="{{route('user.phone.book.contact.index')}}"><i class="lab la-jira me-3"></i>{{ translate('All Numbers')}}</a>
                        </li>
                    </ul>
                </li>

                <li>
                    <a class="ms--1 d--flex align--center {{sidebarMenuActive(['user.email.group.index', 'user.email.contact.index', 'user.email.contact.group'])}} side_bar_eight_list" href="javascript:void(0)"><div><span class="me-4"><i class="fs-5 las la-envelope text--light"></i></span>{{ translate('Email')}}</div><i class="las la-angle-down icon8"></i></a>
                    <ul class="first_eight_child {{menuActive('user.email*',8)}}">
                        <li>
                            <a class="{{menuActive(['user.email.group.index','user.email.contact.group'])}}" href="{{route('user.email.group.index')}}"><i class="lab la-jira me-3"></i>{{ translate('Groups')}}</a>
                            <a class="{{menuActive('user.email.contact.index')}}" href="{{route('user.email.contact.index')}}"><i class="lab la-jira me-3"></i>{{ translate('All Emails')}}</a>
                        </li>
                    </ul>
                </li>
            </ul>

            <h1 class="text-muted ms--1 mb-2 text-capitalize">{{ translate('GATEWAY')}}</h1>
            <ul>
                <li>
                    <a class="ms--1 d--flex align--center {{sidebarMenuActive(['user.gateway.sms.index', 'user.gateway.sms.edit','user.gateway.sms.android.index', 'user.gateway.sms.android.sim.index','user.gateway.sms.sendmethod'])}} side_bar_sms_gw" href="javascript:void(0)">
                        <div>
                            <span class="me-4">
                                <i class="fs-5 las la-sms text--light"></i>
                            </span> 
                            {{ translate('SMS Settings')}}
                        </div>
                        <i class="las la-angle-down icon3"></i>
                    </a>
                    <ul class="first_side_bar_sms_gw {{menuActive('user.gateway.sms*',3)}}">
                        <li>
                            <a class="{{menuActive(['user.gateway.sms.sendmethod'])}}" href="{{route('user.gateway.sms.sendmethod')}}"><i class="lab la-jira me-3"></i> {{ translate('SMS Gateway Method')}}</a>

                            <a class="{{menuActive(['user.gateway.sms.index', 'user.gateway.sms.edit'])}}" href="{{route('user.gateway.sms.index')}}"><i class="lab la-jira me-3"></i> {{ translate('SMS API Gateway')}}</a>
                        
                            <a class="{{menuActive(['user.gateway.sms.android.index', 'user.gateway.sms.android.sim.index'])}}" href="{{route('user.gateway.sms.android.index')}}"><i class="lab la-jira me-3"></i> {{ translate('Android Gateway')}}</a>
                        </li>
                    </ul>
                </li>
 

                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{menuActive(['user.mail.configuration', 'user.mail.edit'])}}" href="{{route('user.mail.configuration')}}">
                        <div>
                            <span class="me-3"><i class="las la-mail-bulk fs-5 text--light me-2"></i></span>{{ translate('Mail Configuration')}}
                        </div>
                    </a>
                </li>

                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{menuActive(['user.gateway.whatsapp.edit','user.gateway.whatsapp.create'])}}" href="{{route('user.gateway.whatsapp.create')}}">
                        <div>
                            <span class="me-3"><i class="lab la-whatsapp fs-5 text--light me-2"></i></span>{{ translate('WhatsApp Settings')}}
                        </div>
                    </a>
                </li>
            </ul>

            <h1 class="text-muted ms--1 mb-2 text-capitalize">{{ translate('REPORTS')}}</h1>
            <ul>
                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{menuActive(['user.transaction.history', 'user.transaction.search'])}}" href="{{route('user.transaction.history')}}">
                        <div>
                            <span class="me-3"><i class="fs-5 las la-credit-card text--light me-2"></i></span>{{ translate('Transaction Logs')}}
                        </div>
                    </a>
                </li>

                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{menuActive(['user.credit.history', 'user.credit.search'])}}" href="{{route('user.credit.history')}}">
                        <div>
                            <span class="me-3"><i class="las la-bars fs-5 text--light me-2"></i></span>{{ translate('SMS Credit Logs')}}
                        </div>
                    </a>
                </li>

                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{menuActive(['user.whatsapp.credit.history', 'user.whatsapp.credit.search'])}}" href="{{route('user.whatsapp.credit.history')}}">
                        <div>
                            <span class="me-3"><i class="las la-bars fs-5 text--light me-2"></i></span>{{ translate('WhatsApp Credit Logs')}}
                        </div>
                    </a>
                </li>

                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{menuActive(['user.credit.email.history', 'user.credit.email.search'])}}" href="{{route('user.credit.email.history')}}">
                        <div>
                            <span class="me-3"><i class="las la-tasks fs-5 text--light me-2"></i></span>{{ translate('Email Credit Logs')}}
                        </div>
                    </a>
                </li>
            </ul>
            <h1 class="text-muted ms--1 mb-2 text-capitalize">{{ translate('DEVELOPER OPTIONS')}}</h1>
            <!-- API Document -->
            <ul>
                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{menuActive('user.generate.api.key')}}" href="{{route('user.generate.api.key')}}">
                        <div>
                            <span class="me-3"><i class="fs-5 las la-key text--light me-2"></i></span> {{ translate('Generate Key')}}
                        </div>
                    </a>
                </li>
                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{menuActive('api.document')}}" href="{{route('api.document')}}">
                        <div>
                            <span class="me-3"><i class="fs-5 las la-code text--light me-2"></i></span> {{ translate('API Document')}}
                        </div>
                    </a>
                </li>
            </ul>

            <h1 class="text-muted ms--1 mb-2 text-capitalize">{{ translate('SUPPORT')}}</h1>
            <ul>
                <li class="side_bar_list d--flex align--center">
                    <a class="ms--1 d--flex align--center {{menuActive(['user.ticket.index', 'user.ticket.detail', 'user.ticket.create'])}}" href="{{route('user.ticket.index')}}">
                        <div>
                            <span class="me-3"><i class="las la-ticket-alt fs-5 text--light me-2"></i></span>{{ translate('Support Tickets')}}
                        </div>
                    </a>
                </li>
            </ul>
        </div> 
    </div>
    <div class="text-center p-1 text-uppercase version">
        <span class="text--primary">©{{ @$general->copyright }}</span>
        <span class="text--success">{{ config('requirements.core.appVersion')}}</span>
    </div>
</div>
