package com.frozenlab.voting.api.models

class Answer {

    var questionId: Int = -1
    var variant: Variants = Variants.REFRAINED

    enum class Variants(val id: Int) {
        AGREED(10),
        DISAGREED(20),
        REFRAINED(30)
    }
}