package com.frozenlab.voting.conductor.controllers

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.viewbinding.ViewBinding
import com.frozenlab.extensions.pushControllerHorizontal
import com.frozenlab.ui.recycler.decoration.SpacingDecoration
import com.frozenlab.voting.R
import com.frozenlab.voting.api.models.Meeting
import com.frozenlab.voting.conductor.controllers.base.BaseController
import com.frozenlab.voting.databinding.ControllerMainNestedMeetingsBinding
import com.frozenlab.voting.recycler.adapter.MeetingAdapter

class MainNestedMeetingsController: BaseController {

    constructor(): super()
    constructor(args: Bundle): super(args)

    private val meetings: ArrayList<Meeting> = ArrayList()

    override val binding: ControllerMainNestedMeetingsBinding get() = _binding as ControllerMainNestedMeetingsBinding

    override fun inflateViewBinding(inflater: LayoutInflater, container: ViewGroup): ViewBinding {
        return ControllerMainNestedMeetingsBinding.inflate(inflater, container, false)
    }

    override fun onViewBound(view: View) {

        val spacing = resources?.getDimensionPixelSize(R.dimen.padding_huge) ?: 0
        binding.recyclerMeetings.addItemDecoration(SpacingDecoration(spacing))
        binding.recyclerMeetings.adapter = MeetingAdapter(meetings).apply {
            onClickListener = { position ->
                val meetingId = meetings[position].id
                apiRequest(
                    votingApi.getQuestions(meetingId),
                    {
                        parentController?.router?.pushControllerHorizontal(MeetingController(meetingId, it))
                    }
                )
            }
        }

        meetings.clear()

        mainActivity.currentUserRealEstates?.also { realEstatesList ->
            for(realEstate in realEstatesList) {
                apiRequest(
                    votingApi.getMeetings(realEstate.houseId),
                    { list ->
                        meetings.addAll(list)
                        binding.recyclerMeetings.adapter?.notifyDataSetChanged()
                    }
                )
            }
        }
    }


}