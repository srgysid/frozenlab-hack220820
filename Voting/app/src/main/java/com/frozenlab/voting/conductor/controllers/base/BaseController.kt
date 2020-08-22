package com.frozenlab.voting.conductor.controllers.base

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import com.frozenlab.api.ApiCommunicator
import com.frozenlab.extensions.hideKeyboard
import com.frozenlab.ui.conductor.controller.common.CommonController
import com.frozenlab.voting.MainActivity
import com.frozenlab.voting.api.VotingApi
import com.frozenlab.voting.api.VotingApiContext
import io.reactivex.rxjava3.core.Completable
import io.reactivex.rxjava3.core.Single

abstract class BaseController: CommonController, ApiCommunicator, VotingApiContext {

    constructor(): super()
    constructor(args: Bundle): super(args)

    override lateinit var votingApi: VotingApi

    protected lateinit var mainActivity: MainActivity

    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup, savedViewState: Bundle?): View {

        mainActivity = activity!! as MainActivity
        votingApi    = mainActivity.votingApi

        mainActivity.hideKeyboard()

        return super.onCreateView(inflater, container, savedViewState)
    }

    override fun apiRequest(completable: Completable, successUnit: (() -> Unit)?, failUnit: ((throwable: Throwable) -> Unit)?, showLoading: Boolean) {
        mainActivity.apiRequest(completable, successUnit, failUnit)
    }

    override fun <T> apiRequest(single: Single<T>, successUnit: ((param: T) -> Unit)?, failUnit: ((throwable: Throwable) -> Unit)?, showLoading: Boolean) {
        mainActivity.apiRequest(single, successUnit, failUnit)
    }
}