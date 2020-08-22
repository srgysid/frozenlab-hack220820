package com.frozenlab.marketplace.conductor.controller.base

import android.os.Bundle
import com.bluelinelabs.conductor.ControllerChangeHandler
import com.bluelinelabs.conductor.ControllerChangeType
import com.frozenlab.voting.conductor.controllers.base.BaseController

abstract class ToolbarController: BaseController {

    constructor(): super()
    constructor(args: Bundle): super(args)

    abstract val showToolbar: Boolean
    open     val backIsClose: Boolean = false

    override fun onChangeEnded(changeHandler: ControllerChangeHandler, changeType: ControllerChangeType) {
        super.onChangeEnded(changeHandler, changeType)

        setOptionsMenuHidden(!changeType.isEnter)

        if(changeType.isEnter) {
            setupToolbar()
        }
    }

    private fun setupToolbar() {

        val actionBar = mainActivity.supportActionBar ?: return

        if(showToolbar) {
/*
            val (titleResId, iconResId) = if(router.backstackSize > 1) {

                if(backIsClose) {
                    Pair(R.string.close, R.drawable.ic_close_black_12dp)
                } else {
                    Pair(R.string.back, R.drawable.ic_chevron_back_black_12dp)
                }

            } else {
                Pair(View.NO_ID, R.drawable.ic_menu_black_24dp)
            }*/
/*
            if(titleResId != View.NO_ID)
                mainActivity.binding.textToolbarTitle.setText(titleResId)
            else
                mainActivity.binding.textToolbarTitle.text = null

            mainActivity.binding.textToolbarTitle.setCompoundDrawablesRelativeWithIntrinsicBounds(iconResId, 0, 0, 0)
*/
            actionBar.show()

        } else {
            actionBar.hide()
        }
    }
}