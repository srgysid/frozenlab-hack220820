package com.frozenlab.voting.conductor.controllers

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.viewbinding.ViewBinding
import com.frozenlab.voting.R
import com.frozenlab.voting.api.models.Meeting
import com.frozenlab.voting.conductor.controllers.base.BaseController
import com.frozenlab.voting.custom.applyMask
import com.frozenlab.voting.databinding.ControllerMainNestedProfileBinding

class MainNestedProfileController: BaseController {

    constructor(): super()
    constructor(args: Bundle): super(args)

    private val meetings: ArrayList<Meeting> = ArrayList()

    override val binding: ControllerMainNestedProfileBinding get() = _binding as ControllerMainNestedProfileBinding

    override fun inflateViewBinding(inflater: LayoutInflater, container: ViewGroup): ViewBinding {
        return ControllerMainNestedProfileBinding.inflate(inflater, container, false)
    }

    override fun onViewBound(view: View) {

        mainActivity.currentUserProfile?.also { userProfile ->  
            binding.textUserName.text = userProfile.name.fullName
            binding.textEmail.text = userProfile.email
            binding.textPhone.text = userProfile.phone.applyMask(mainActivity.getString(R.string.mask_phone))
        }

        mainActivity.currentUserRealEstates?.also { realEstates ->
            if(realEstates.size > 0) {
                binding.textRealEstate.text = realEstates[0].toString()
            }
        }
    }


}