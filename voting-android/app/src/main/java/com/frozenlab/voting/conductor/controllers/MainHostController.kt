package com.frozenlab.voting.conductor.controllers

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.viewbinding.ViewBinding
import androidx.viewpager.widget.ViewPager
import com.frozenlab.marketplace.conductor.controller.base.ToolbarController
import com.frozenlab.voting.R
import com.frozenlab.voting.conductor.pager.MainRouterPagerAdapter
import com.frozenlab.voting.databinding.ControllerMainHostBinding
import com.google.android.material.bottomnavigation.BottomNavigationView

class MainHostController: ToolbarController {

    constructor() : super() {}
    constructor(args: Bundle) : super(args)

    override val showToolbar: Boolean = true

    override val binding: ControllerMainHostBinding get() = _binding!! as ControllerMainHostBinding
    override fun inflateViewBinding(inflater: LayoutInflater, container: ViewGroup): ViewBinding {
        return ControllerMainHostBinding.inflate(inflater, container, false)
    }

    override fun onViewBound(view: View) {

        if(binding.viewPager.adapter == null) {

            binding.viewPager.adapter = MainRouterPagerAdapter(this)
            binding.viewPager.addOnPageChangeListener(viewPagerChangePageListener)

            binding.bottomNavigationView.setOnNavigationItemSelectedListener(bottomNavigationSelectListener)
            binding.bottomNavigationView.selectedItemId = R.id.menu_item_meetings
        }
    }

    private val viewPagerChangePageListener = object: ViewPager.OnPageChangeListener {

        override fun onPageSelected(position: Int) {

            binding.bottomNavigationView.selectedItemId = when (position) {
                0 -> R.id.menu_item_meetings
                1 -> R.id.menu_item_notifications
                2 -> R.id.menu_item_profile
                else -> 0
            }
        }

        override fun onPageScrollStateChanged(state: Int) {}
        override fun onPageScrolled(position: Int, positionOffset: Float, positionOffsetPixels: Int) {}
    }

    private val bottomNavigationSelectListener = BottomNavigationView.OnNavigationItemSelectedListener { menuItem ->

        val titleId = when (menuItem.itemId) {
            R.id.menu_item_meetings      -> R.string.meetings
            R.id.menu_item_notifications -> R.string.notifcations
            R.id.menu_item_profile       -> R.string.profile
            else -> 0
        }

        binding.viewPager.currentItem = (binding.viewPager.adapter as MainRouterPagerAdapter).getPositionByTitleResId(titleId) ?: 0

        return@OnNavigationItemSelectedListener true
    }
}