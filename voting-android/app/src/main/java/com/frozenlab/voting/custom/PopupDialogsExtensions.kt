package com.frozenlab.voting.custom

import android.app.Activity
import androidx.annotation.StringRes
import com.frozenlab.voting.databinding.PopupConfirmCancelButtonBinding
import com.frozenlab.voting.databinding.PopupOkButtonBinding


fun Activity.showAlertOkButton(@StringRes messageId: Int, @StringRes buttonTextId: Int? = null, okButtonAction: (() -> Unit)? = null) {

    val message    = this.getString(messageId)
    val buttonText = buttonTextId?.let {
        this.getString(buttonTextId)
    }

    this.showAlertOkButton(message, buttonText, okButtonAction)
}

fun Activity.showAlertOkButton(message: String, buttonText: String? = null, okButtonAction: (() -> Unit)? = null) {

    val binding = PopupOkButtonBinding.inflate(this.layoutInflater, null, false)
    val dialog = android.app.AlertDialog.Builder(this)
        .setView(binding.root)
        .create()

    binding.textMessage.text = message
    buttonText?.also { binding.buttonOk.text = it }
    binding.buttonOk.setOnClickListener {
        okButtonAction?.invoke()
        dialog.dismiss()
    }

    dialog.show()
}

fun Activity.showAlertConfirmCancelButton(@StringRes messageId: Int, @StringRes confirmButtonTextId: Int? = null, @StringRes cancelButtonTextId: Int? = null, confirmButtonAction: (() -> Unit)? = null) {

    val message           = this.getString(messageId)
    val confirmButtonText = confirmButtonTextId?.let { this.getString(confirmButtonTextId) }
    val cancelButtonText  = cancelButtonTextId?.let { this.getString(cancelButtonTextId) }

    this.showAlertConfirmCancelButton(message, confirmButtonText, cancelButtonText, confirmButtonAction)
}

fun Activity.showAlertConfirmCancelButton(message: String, confirmButtonText: String? = null, cancelButtonText: String? = null, confirmButtonAction: (() -> Unit)? = null) {

    val binding = PopupConfirmCancelButtonBinding.inflate(this.layoutInflater, null, false)
    val dialog = android.app.AlertDialog.Builder(this)
        .setView(binding.root)
        .create()

    binding.textMessage.text = message
    confirmButtonText?.also { binding.buttonConfirm.text = it }
    cancelButtonText?.also { binding.buttonCancel.text = it }

    binding.buttonConfirm.setOnClickListener {
        confirmButtonAction?.invoke()
        dialog.dismiss()
    }

    binding.buttonCancel.setOnClickListener {
        dialog.dismiss()
    }

    dialog.show()
}