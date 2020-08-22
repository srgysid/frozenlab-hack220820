package com.frozenlab.voting.custom

import com.redmadrobot.inputmask.helper.Mask
import com.redmadrobot.inputmask.model.CaretString
import java.util.*

fun String.applyMask(mask: String): String {
    return Mask.getOrCreate(mask, ArrayList())
        .apply(CaretString(this, 0, CaretString.CaretGravity.FORWARD(true)))
        .formattedText
        .string
}