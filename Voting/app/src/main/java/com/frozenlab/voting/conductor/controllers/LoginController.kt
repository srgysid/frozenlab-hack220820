package com.frozenlab.voting.conductor.controllers

import android.os.Bundle
import android.view.KeyEvent
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.viewbinding.ViewBinding
import com.frozenlab.api.toApiError
import com.frozenlab.extensions.showToast
import com.frozenlab.marketplace.api.requests.LoginRequest
import com.frozenlab.ui.addTextWatcherForDisablingError
import com.frozenlab.voting.R
import com.frozenlab.voting.api.isCorrectPhone
import com.frozenlab.voting.api.responses.NewAccessTokenResponse
import com.frozenlab.voting.api.toNormalizedPhoneNumber
import com.frozenlab.voting.conductor.controllers.base.BaseController
import com.frozenlab.voting.databinding.ControllerLoginBinding
import com.redmadrobot.inputmask.MaskedTextChangedListener

class LoginController: BaseController {

    constructor() : super() {}
    constructor(args: Bundle) : super(args)

    override val binding: ControllerLoginBinding get() = _binding!! as ControllerLoginBinding

    override fun inflateViewBinding(inflater: LayoutInflater, container: ViewGroup): ViewBinding {
        return ControllerLoginBinding.inflate(inflater, container, false)
    }

    override fun onViewBound(view: View) {

        val listener = MaskedTextChangedListener(mainActivity.getString(R.string.mask_phone), binding.editablePhone)
        binding.editablePhone.addTextChangedListener(listener)
        binding.editablePhone.onFocusChangeListener = listener
        binding.editablePhone.addTextWatcherForDisablingError(binding.wrapperPhone)

        binding.editablePassword.setOnKeyListener { _, _, keyEvent ->
            if (keyEvent.action == KeyEvent.ACTION_UP && keyEvent.keyCode == KeyEvent.KEYCODE_ENTER) {
                binding.buttonSingIn.callOnClick()
                true
            } else {
                false
            }
        }

        binding.buttonSingIn.setOnClickListener(signInClickListener)
        binding.textSignUp.setOnClickListener(signUpClickListener)
    }

    private val restorePasswordClickListener = View.OnClickListener {
        //router.pushControllerHorizontal(RestorePasswordController())
    }

    private val signInClickListener = View.OnClickListener {

        if (!binding.editablePhone.text.toString().isCorrectPhone()) {

            binding.wrapperPhone.isErrorEnabled = true
            binding.editablePhone.error = mainActivity.getString(R.string.available_phone_formats)

            mainActivity.showToast(R.string.error_phone_wrong)
            return@OnClickListener
        }

        val request = LoginRequest().apply {
            phone = binding.editablePhone.text.toString().toNormalizedPhoneNumber()
            password = binding.editablePassword.text.toString()
        }

        this.apiRequest(
            votingApi.login(request),
            { loginResponse -> onLoginSuccess(loginResponse) },
            { throwable -> onLoginFail(throwable) }
        )
    }

    private val signUpClickListener = View.OnClickListener {
        //router.pushControllerHorizontal(RegistrationController())
    }

    private fun onLoginSuccess(newAccessTokenResponse: NewAccessTokenResponse) {

        if(newAccessTokenResponse.accessToken.isEmpty()) {

            onLoginFail(Throwable(mainActivity.getString(R.string.error_login)))
            return
        }

        mainActivity.accessToken = newAccessTokenResponse.accessToken

        mainActivity.login()
    }

    private fun onLoginFail(throwable: Throwable) {
        mainActivity.showToast(throwable.toApiError().message, true)
    }
}