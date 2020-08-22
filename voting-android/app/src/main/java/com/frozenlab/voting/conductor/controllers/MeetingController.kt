package com.frozenlab.voting.conductor.controllers

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.viewbinding.ViewBinding
import com.frozenlab.voting.api.models.Answer
import com.frozenlab.voting.api.models.Question
import com.frozenlab.voting.conductor.controllers.base.BaseController
import com.frozenlab.voting.databinding.ControllerMeetingBinding
import com.frozenlab.voting.recycler.adapter.QuestionAdapter
import com.google.gson.GsonBuilder

class MeetingController: BaseController {

    companion object {
        private const val KEY_SAVED_MEETING_ID = "saved_meeting_id"
        private const val KEY_SAVED_QUESTIONS = "saved_questions"
    }

    private val meetingId: Int
    private val questions: ArrayList<Question>
    private val answers: ArrayList<Answer> = ArrayList()

    constructor(meetingId: Int, questions: ArrayList<Question>): super() {

        this.meetingId = meetingId
        this.questions = questions

        this.questions.sortBy { it.order }

        val gson = GsonBuilder().create()

        args.putInt(KEY_SAVED_MEETING_ID, this.meetingId)

        args.putStringArrayList(
            KEY_SAVED_QUESTIONS,
            this.questions.map { gson.toJson(it) } as java.util.ArrayList<String>
        )
    }
    constructor(args: Bundle): super(args) {

        meetingId = args.getInt(KEY_SAVED_MEETING_ID)
        questions = args.getStringArrayList(KEY_SAVED_QUESTIONS)?.let { list ->
            val gson = GsonBuilder().create()
            list.map { gson.fromJson(it, Question::class.java) } as ArrayList<Question>
        } ?: ArrayList()
    }

    override val binding: ControllerMeetingBinding get() = _binding as ControllerMeetingBinding

    override fun inflateViewBinding(inflater: LayoutInflater, container: ViewGroup): ViewBinding {
        return ControllerMeetingBinding.inflate(inflater, container, false)
    }

    override fun onViewBound(view: View) {

        binding.recyclerQuestions.adapter = QuestionAdapter(questions, answers).apply {
            onAgreedClickListener = { position ->
                answers.find { it.questionId == questions[position].id }?.let {
                    it.variant = Answer.Variants.AGREED
                } ?: let { answers.add(Answer().apply {
                    questionId = questions[position].id
                    variant = Answer.Variants.AGREED
                }) }
            }

            onDisagreedClickListener = { position ->
                answers.find { it.questionId == questions[position].id }?.let {
                    it.variant = Answer.Variants.DISAGREED
                } ?: let { answers.add(Answer().apply {
                    questionId = questions[position].id
                    variant = Answer.Variants.DISAGREED
                }) }
            }

            onRefrainedClickListener = { position ->
                answers.find { it.questionId == questions[position].id }?.let {
                    it.variant = Answer.Variants.REFRAINED
                } ?: let { answers.add(Answer().apply {
                    questionId = questions[position].id
                    variant = Answer.Variants.REFRAINED
                }) }
            }
        }

        binding.buttonSend.setOnClickListener {
            apiRequest(
                votingApi.sendAnswers(meetingId, answers),
                { router.popCurrentController() }
            )
        }
    }
}