<?php

namespace Modules\DomainReseller\Http\Enums;

enum PaymentRouteEnum
{
    const SUCCESS_ROUTE = 'tenant.admin.domain-reseller.payment.success';
    const CANCEL_ROUTE = 'tenant.admin.domain-reseller.payment.cancel';
    const STATIC_CANCEL_ROUTE = 'tenant.admin.domain-reseller.payment.cancel.static';
    const CONFIRM_ROUTE = 'tenant.admin.domain-reseller.payment.confirm';
}
