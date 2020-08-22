package com.frozenlab.voting.recycler.adapter

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.frozenlab.extensions.toFormattedString
import com.frozenlab.voting.R
import com.frozenlab.voting.api.models.Meeting
import com.frozenlab.voting.databinding.AdapterMeetingBinding

class MeetingAdapter(private val items: ArrayList<Meeting>): RecyclerView.Adapter<MeetingAdapter.MeetingViewHolder>() {

    override fun getItemCount(): Int = items.size

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): MeetingViewHolder {
        return MeetingViewHolder(
            AdapterMeetingBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        )
    }

    override fun onBindViewHolder(holder: MeetingViewHolder, position: Int) {

        val item = items[position]
        val context = holder.itemView.context

        val dateFormat = context.getString(R.string.format_date)
        val dateBeginStr = item.startedAt?.toFormattedString(dateFormat) ?: "---"
        val dateFinishStr = item.finishedAt?.toFormattedString(dateFormat) ?: "---"

        holder.binding.textNumber.text = context.getString(R.string.template_reg_num, item.registrationNumber)
        holder.binding.textDate.text = context.getString(R.string.template_date, dateBeginStr, dateFinishStr)
        holder.binding.textType.text = context.getString(R.string.template_type, item.typeName)
        holder.binding.textAddress.text = context.getString(R.string.template_address, "${item.streetFull}, ${item.houseNumber}")
    }

    var onClickListener: ((position: Int) -> Unit)? = null

    inner class MeetingViewHolder(val binding: AdapterMeetingBinding): RecyclerView.ViewHolder(binding.root) {
        init {
            binding.root.setOnClickListener {
                onClickListener?.invoke(adapterPosition)
            }
        }
    }

}