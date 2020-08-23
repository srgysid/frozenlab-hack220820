package com.frozenlab.voting.recycler.adapter

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.frozenlab.voting.api.models.Answer
import com.frozenlab.voting.api.models.Question
import com.frozenlab.voting.databinding.AdapterQuestionBinding

class QuestionAdapter(private val items: ArrayList<Question>, private val answers: ArrayList<Answer>): RecyclerView.Adapter<QuestionAdapter.QuestionViewHolder>() {

    override fun getItemCount(): Int = items.size

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): QuestionViewHolder {
        return QuestionViewHolder(
            AdapterQuestionBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        )
    }

    override fun onBindViewHolder(holder: QuestionViewHolder, position: Int) {

        val item = items[position]
        val context = holder.itemView.context

        holder.binding.textQuestion.text = item.proposal

        answers.find { it.questionId == item.id }?.let {
            when(it.variant) {
                Answer.Variants.AGREED -> {
                    holder.binding.buttonAgreed.isActivated = true
                    holder.binding.buttonDisagreed.isActivated = false
                    holder.binding.buttonRefrained.isActivated = false
                }
                Answer.Variants.DISAGREED -> {
                    holder.binding.buttonAgreed.isActivated = false
                    holder.binding.buttonDisagreed.isActivated = true
                    holder.binding.buttonRefrained.isActivated = false
                }
                Answer.Variants.REFRAINED -> {
                    holder.binding.buttonAgreed.isActivated = false
                    holder.binding.buttonDisagreed.isActivated = false
                    holder.binding.buttonRefrained.isActivated = true
                }
            }
        }
    }

    var onAgreedClickListener: ((position: Int) -> Unit)? = null
    var onDisagreedClickListener: ((position: Int) -> Unit)? = null
    var onRefrainedClickListener: ((position: Int) -> Unit)? = null

    inner class QuestionViewHolder(val binding: AdapterQuestionBinding): RecyclerView.ViewHolder(binding.root) {
        init {
            binding.buttonAgreed.setOnClickListener {
                onAgreedClickListener?.invoke(adapterPosition)
                notifyItemChanged(adapterPosition)
            }

            binding.buttonDisagreed.setOnClickListener {
                onDisagreedClickListener?.invoke(adapterPosition)
                notifyItemChanged(adapterPosition)
            }

            binding.buttonRefrained.setOnClickListener {
                onRefrainedClickListener?.invoke(adapterPosition)
                notifyItemChanged(adapterPosition)
            }
        }
    }

}