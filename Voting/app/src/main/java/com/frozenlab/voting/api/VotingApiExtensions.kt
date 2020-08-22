package com.frozenlab.voting.api

import android.net.Uri
import android.telephony.PhoneNumberUtils
import com.bumptech.glide.load.model.GlideUrl
import com.frozenlab.extensions.glideUrlWithHeaders
import com.frozenlab.voting.Preferences
import java.util.*

fun Uri.glideUrlWithAccessToken(): GlideUrl =
    this.glideUrlWithHeaders(hashMapOf(Pair("Authorization", "Bearer ${Preferences.accessToken}")))

fun String.toNormalizedPhoneNumber(lengthWithoutPrefix: Int = 10): String {

    val normalizedPhone = PhoneNumberUtils.formatNumberToE164(this, Locale.getDefault().country) ?: return ""

    return if(normalizedPhone.length > lengthWithoutPrefix) {
        normalizedPhone.takeLast(lengthWithoutPrefix)
    } else {
        normalizedPhone
    }
}

fun String.isCorrectPhone(): Boolean {
    return (PhoneNumberUtils.formatNumberToE164(this, Locale.getDefault().country) != null)
}
