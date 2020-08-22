package com.frozenlab.voting.conductor.pager

import com.bluelinelabs.conductor.Controller
import com.frozenlab.ui.conductor.pager.common.CommonRouterPagerAdapter
import com.frozenlab.ui.models.RouterPage
import com.frozenlab.voting.R
import com.frozenlab.voting.conductor.controllers.MainNestedMeetingsController
import com.frozenlab.voting.conductor.controllers.MainNestedNotificationsController
import com.frozenlab.voting.conductor.controllers.MainNestedProfileController

class MainRouterPagerAdapter(host: Controller) : CommonRouterPagerAdapter(host) {

    override val pages: ArrayList<RouterPage> = arrayListOf(
        RouterPage( R.string.meetings, MainNestedMeetingsController::class.java),
        RouterPage( R.string.notifcations, MainNestedNotificationsController::class.java),
        RouterPage( R.string.profile,  MainNestedProfileController::class.java)
    )
}