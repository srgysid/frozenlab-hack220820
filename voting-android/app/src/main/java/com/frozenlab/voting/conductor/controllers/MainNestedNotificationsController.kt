package com.frozenlab.voting.conductor.controllers

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.viewbinding.ViewBinding
import com.frozenlab.voting.conductor.controllers.base.BaseController
import com.frozenlab.voting.databinding.ControllerMainNestedNotificationsBinding

class MainNestedNotificationsController: BaseController {

    constructor(): super()
    constructor(args: Bundle): super(args)

    override val binding: ControllerMainNestedNotificationsBinding get() = _binding as ControllerMainNestedNotificationsBinding

    override fun inflateViewBinding(inflater: LayoutInflater, container: ViewGroup): ViewBinding {
        return ControllerMainNestedNotificationsBinding.inflate(inflater, container, false)
    }

    override fun onViewBound(view: View) {

    }


}